<?php
require_once __DIR__ . '/../config.php';

try {
    $conn = new PDO(DB_DSN, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $ex) {
    $conn = null;
}

$communes = [];
$communes_et_quartiers = [];

if ($conn) {
    try {
        $communes = $conn->query('SELECT "commune" FROM public.communes_abidjan ORDER BY "commune"')
                         ->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $ex) { error_log($ex->getMessage()); }

    try {
        $rows = $conn->query('SELECT "commune", nom_quart FROM public.quartiers_abidjan ORDER BY "commune", nom_quart')
                     ->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            if (!isset($communes_et_quartiers[$row['commune']])) {
                $communes_et_quartiers[$row['commune']] = [];
            }
            if (!empty($row['nom_quart'])) {
                $communes_et_quartiers[$row['commune']][] = $row['nom_quart'];
            }
        }
    } catch (PDOException $ex) { error_log($ex->getMessage()); }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Statistiques — Inondation</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">

  <style>
    /* Map fills the content-wrapper */
    .content-wrapper {
      padding: 0 !important;
      position: relative;
      min-height: 100vh;
    }

    #mapid {
      width: 100%;
      height: 100vh;
    }

    /* Floating filter panel on the map */
    .map-filter-panel {
      position: absolute;
      top: 15px;
      left: 15px;
      z-index: 1000;
      background: rgba(34, 45, 50, 0.93);
      padding: 15px;
      border-radius: 6px;
      min-width: 220px;
      max-width: 260px;
      color: #b8c7ce;
      box-shadow: 0 2px 12px rgba(0,0,0,0.4);
    }

    .map-filter-panel h4 {
      color: #fff;
      font-size: 14px;
      margin: 0 0 12px;
      border-bottom: 1px solid #3c4b57;
      padding-bottom: 8px;
    }

    .map-filter-panel label {
      font-size: 12px;
      color: #b8c7ce;
      margin-bottom: 3px;
      display: block;
    }

    .map-filter-panel select {
      width: 100%;
      padding: 6px 8px;
      background-color: #36414c;
      color: #fff;
      border: 1px solid #4a5a66;
      border-radius: 4px;
      margin-bottom: 10px;
      font-size: 13px;
    }

    .info.legend {
      background: white;
      padding: 6px 8px;
      font-size: 13px;
      box-shadow: 0 0 15px rgba(0,0,0,0.2);
      border-radius: 5px;
    }

    .info.legend i {
      width: 18px;
      height: 18px;
      float: left;
      margin-right: 8px;
      opacity: 0.7;
    }
  </style>
</head>

<body class="skin-blue sidebar-mini">
<div class="wrapper">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">

    <!-- Floating filter panel -->
    <div class="map-filter-panel">
      <h4><i class="fa fa-filter"></i> Filtres</h4>

      <label>Commune</label>
      <select id="commune-select">
        <option value="Abidjan" selected>Abidjan (toutes)</option>
        <?php foreach ($communes_et_quartiers as $commune => $quartiers): ?>
          <option value="<?= htmlspecialchars($commune) ?>"><?= htmlspecialchars($commune) ?></option>
        <?php endforeach; ?>
      </select>

      <label>Quartier</label>
      <select id="quartier-select" disabled>
        <option value="Tous">Tous les quartiers</option>
      </select>

      <label>Type de risque</label>
      <select id="risque-select">
        <option value="Total" selected>Tous les risques</option>
        <option value="Inondation">Inondation</option>
        <option value="Erosion">Érosion</option>
        <option value="Eboulement">Éboulement</option>
        <option value="Effondrement">Effondrement</option>
      </select>
    </div>

    <div id="mapid"></div>

  </div><!-- /.content-wrapper -->

</div><!-- /.wrapper -->

<script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-groupedlayercontrol/0.6.1/leaflet.groupedlayercontrol.min.js"></script>
<script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="../dist/js/app.min.js"></script>

<script>
    var communesQuartiers = {};
    var risquesParQuartier = {};
    var maxRisques = 0;
    var geojsonDataCommunes = null;
    var geojsonDataQuartiers = null;
    var currentGeoJsonLayer = null;

    var carte = L.map('mapid').setView([5.3294815, -3.9919594], 11);

    var osm          = L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(carte);
    var GoogleStreets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3'] });
    var GoogleHybrid  = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0','mt1','mt2','mt3'] });
    L.control.layers({ OpenStreetMap: osm, GoogleMap: GoogleStreets, Satellite: GoogleHybrid }).addTo(carte);
    L.control.scale().addTo(carte);

    function getColorForRisk(d) {
        if (maxRisques === 0) return '#FFEDA0';
        return d > maxRisques * 0.75 ? '#800026' :
               d > maxRisques * 0.50 ? '#BD0026' :
               d > maxRisques * 0.25 ? '#E31A1C' :
               d > 0                 ? '#FC4E2A' : '#FFEDA0';
    }

    function loadCommunesAndQuartiersDropdowns() {
        $.ajax({
            url: 'ajax.php', type: 'GET',
            data: { elemid: 'get_communes_quartiers' }, dataType: 'json',
            success: function(r) {
                if (r && r.communes && r.communes_et_quartiers) {
                    communesQuartiers = r.communes_et_quartiers;
                    updateMapAndRisks();
                }
            },
            error: function(x, s, e) { console.error('Erreur AJAX communes:', s, e); }
        });
    }

    function loadInitialGeoJSONData() {
        $.getJSON('data/communes_abidjan.geojson')
            .done(function(g) {
                geojsonDataCommunes = g;
                if (geojsonDataQuartiers) updateMapAndRisks();
            })
            .fail(function(x, s) { console.error('Erreur GeoJSON communes:', s); });

        $.getJSON('data/quartiers_abidjan.geojson')
            .done(function(g) {
                geojsonDataQuartiers = g;
                if (geojsonDataCommunes) updateMapAndRisks();
            })
            .fail(function(x, s) { console.error('Erreur GeoJSON quartiers:', s); });
    }

    function updateMapAndRisks() {
        var commune  = $('#commune-select').val();
        var quartier = $('#quartier-select').val();
        var risque   = $('#risque-select').val();

        $.ajax({
            url: 'ajax.php', type: 'GET',
            data: { elemid: 'get_risks_by_quartier', commune: commune, quartier: quartier, risque: risque },
            dataType: 'json',
            success: function(r) {
                if (r.success && r.data) {
                    risquesParQuartier = r.data;
                    maxRisques = r.max_risques;
                } else {
                    risquesParQuartier = {};
                    maxRisques = 0;
                }
                drawMapLayers();
                updateLegend();
            },
            error: function() {
                risquesParQuartier = {};
                maxRisques = 0;
                drawMapLayers();
                updateLegend();
            }
        });
    }

    function drawMapLayers() {
        if (currentGeoJsonLayer) carte.removeLayer(currentGeoJsonLayer);

        var commune  = $('#commune-select').val();
        var quartier = $('#quartier-select').val();

        if (commune === 'Abidjan') {
            if (!geojsonDataCommunes) return;
            currentGeoJsonLayer = L.geoJson(geojsonDataCommunes.features, {
                style: function() {
                    return { fillColor: '#FFEDA0', weight: 1, opacity: 1, color: 'white', dashArray: '3', fillOpacity: 0.4 };
                },
                onEachFeature: function(feature, layer) {
                    layer.bindPopup('<b>Commune :</b> ' + feature.properties.commune);
                    layer.on('click', function(e) { carte.fitBounds(e.target.getBounds()); });
                }
            }).addTo(carte);
        } else {
            if (!geojsonDataQuartiers) return;
            var features = geojsonDataQuartiers.features.filter(function(f) { return f.properties.commune === commune; });
            if (quartier !== 'Tous') {
                features = features.filter(function(f) { return f.properties.nom_quartier === quartier; });
            }
            var selRisque = $('#risque-select').val();
            currentGeoJsonLayer = L.geoJson(features, {
                style: function(feature) {
                    var nom  = feature.properties.nom_quartier;
                    var cnt  = 0;
                    if (risquesParQuartier[nom]) {
                        cnt = selRisque === 'Total'
                              ? risquesParQuartier[nom].total
                              : (risquesParQuartier[nom].par_type[selRisque] || 0);
                    }
                    return { fillColor: getColorForRisk(cnt), weight: 1, opacity: 1,
                             color: 'white', dashArray: '3', fillOpacity: cnt > 0 ? 0.7 : 0.4 };
                },
                onEachFeature: function(feature, layer) {
                    var nom  = feature.properties.nom_quartier;
                    var rd   = risquesParQuartier[nom] || { total: 0, par_type: {} };
                    var sel  = $('#risque-select').val();
                    var info = sel === 'Total'
                               ? '<b>Risques totaux :</b> ' + rd.total
                               : '<b>' + sel + ' :</b> ' + (rd.par_type[sel] || 0);
                    layer.bindPopup('<b>Quartier :</b> ' + nom + '<br>' + info);
                    layer.on('click', function(e) { carte.fitBounds(e.target.getBounds()); });
                }
            }).addTo(carte);
        }

        if (currentGeoJsonLayer && currentGeoJsonLayer.getLayers().length > 0) {
            carte.fitBounds(currentGeoJsonLayer.getBounds());
        }
    }

    var currentLegend = null;
    function updateLegend() {
        if (currentLegend) carte.removeControl(currentLegend);
        currentLegend = L.control({ position: 'bottomright' });
        currentLegend.onAdd = function() {
            var div    = L.DomUtil.create('div', 'info legend');
            var grades = [0, maxRisques * 0.25, maxRisques * 0.50, maxRisques * 0.75];
            var labels = ['<h4>Niveau de risque</h4>'];
            if (maxRisques === 0) {
                labels.push('<i style="background:' + getColorForRisk(0) + '"></i> Aucun risque');
            } else {
                labels.push('<i style="background:' + getColorForRisk(0) + '"></i> Aucun');
                labels.push('<i style="background:' + getColorForRisk(grades[0]+1) + '"></i> ' + Math.round(grades[0]+1) + ' – ' + Math.round(grades[1]));
                labels.push('<i style="background:' + getColorForRisk(grades[1]+1) + '"></i> ' + Math.round(grades[1]+1) + ' – ' + Math.round(grades[2]));
                labels.push('<i style="background:' + getColorForRisk(grades[2]+1) + '"></i> ' + Math.round(grades[2]+1) + ' – ' + Math.round(grades[3]));
                labels.push('<i style="background:' + getColorForRisk(maxRisques) + '"></i> ' + Math.round(grades[3]+1) + ' +');
            }
            div.innerHTML = labels.join('<br>');
            return div;
        };
        currentLegend.addTo(carte);
    }

    $('#commune-select').on('change', function() {
        var commune = $(this).val();
        var $qs = $('#quartier-select').empty().append('<option value="Tous">Tous les quartiers</option>');
        if (commune !== 'Abidjan') {
            $qs.prop('disabled', false);
            var quartiers = communesQuartiers[commune];
            if (quartiers) {
                $.each(quartiers, function(i, q) { $qs.append('<option value="'+q+'">'+q+'</option>'); });
            }
        } else {
            $qs.prop('disabled', true);
        }
        updateMapAndRisks();
    });

    $('#quartier-select').on('change', function() { updateMapAndRisks(); });
    $('#risque-select').on('change',   function() { updateMapAndRisks(); });

    $(document).ready(function() {
        loadInitialGeoJSONData();
        loadCommunesAndQuartiersDropdowns();
    });
</script>
</body>
</html>
