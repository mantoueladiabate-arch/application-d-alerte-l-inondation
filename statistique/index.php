<?php

// Fonction de connexion à la base de données
function connect() {
    $conn = null;
    try {
        $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation', 'postgres', 'postgres');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $ex) {
        echo 'Échec de la connexion à la base de données : ' . $ex->getMessage();
        exit();
    }
    return $conn;
}

$conn = connect();
if (!$conn) {
    exit();
}

// 1. Récupération de toutes les communes et de leurs quartiers
$sql_communes = 'SELECT "commune" FROM public.communes_abidjan ORDER BY "commune"';
try {
    $result_communes = $conn->query($sql_communes);
    $communes = $result_communes->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $ex) {
    error_log('Erreur de requête SQL (communes) : ' . $ex->getMessage());
    $communes = [];
}

// Récupération de la liste des quartiers par commune depuis la table du shapefile
$sql_communes_quartiers = 'SELECT "commune", nom_quart FROM public.quartiers_abidjan ORDER BY "commune", nom_quart';
try {
    $result_quartiers = $conn->query($sql_communes_quartiers);
    $communes_et_quartiers = [];
    while ($row = $result_quartiers->fetch(PDO::FETCH_ASSOC)) {
        if (!isset($communes_et_quartiers[$row['commune']])) {
            $communes_et_quartiers[$row['commune']] = [];
        }
        if (!empty($row['nom_quart'])) { // La colonne est "nom_quart" dans le shapefile
            $communes_et_quartiers[$row['commune']][] = $row['nom_quart'];
        }
    }
} catch (PDOException $ex) {
    error_log('Erreur lors de la récupération des statistiques de risques par quartier : ' . $ex->getMessage());
    $risques_par_quartier = [];
}

// Détermination de la valeur maximale des risques pour la légende
$max_risques = 0;
foreach ($risques_par_quartier as $data) {
    if ($data['total'] > $max_risques) {
        $max_risques = $data['total'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Inondation à cocody</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-groupedlayercontrol/0.6.1/leaflet.groupedlayercontrol.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">

    <style>
        /* Styles personnalisés */
        .wrapper {
            display: flex;
            height: 100vh;
        }
        .main-sidebar {
            width: 250px;
            background-color: #222d32;
            color: #b8c7ce;
            padding-top: 20px;
        }
        .content-wrapper {
            flex-grow: 1;
        }
        #mapid {
            width: 100%;
            height: 100%;
        }
        .form-group.filter-group {
            padding: 10px;
            border-bottom: 1px solid #3c4b57;
        }
        .form-control {
            background-color: #36414c;
            color: white;
            border: none;
        }
        .info.legend {
            background: white;
            padding: 6px 8px;
            font-size: 14px;
            font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;
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
         .logo-lg {
            text-align: center;     /* Centre horizontalement */
            font-size: 24px;        /* Taille du texte */
             }
            
    </style>
</head>
<body>
  
    <div class="wrapper">
        <aside class="main-sidebar">
            <section class="sidebar">
                <ul class="sidebar-menu">
                    <li class="header"></li>
                     <a href="../index.php" class="logo">
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>Acceuil</b></span>
        </a>
                    <li class="form-group filter-group">
                        <label for="commune-select">Sélectionner une commune</label>
                        <select id="commune-select" class="form-control">
                            <option value="Abidjan" selected>Communes</option>
                            <?php foreach ($communes_et_quartiers as $commune => $quartiers): ?>
                                <option value="<?php echo htmlspecialchars($commune); ?>"><?php echo htmlspecialchars($commune); ?></option>
                            <?php endforeach; ?>
                                
                        </select>
                   <li class="form-group filter-group">
                        <label for="quartier-select">Sélectionner un quartier</label>
                        <select id="quartier-select" class="form-control" disabled>
                            <option value="Tous">Tous les quartiers</option>
                        </select>
                    </li>
                    <li class="form-group filter-group">
                        <label for="risque-select">Sélectionner le type de risque</label>
                        <select id="risque-select" class="form-control">
                            <option value="Total" selected>Tous les risques</option>
                            <option value="Inondation">Inondation</option>
                            <option value="Erosion">Érosion</option>
                            <option value="Eboulement">Éboulement</option>
                            <option value="Effondrement">Effondrement</option>
                        </select>
                    </li>
                </ul>
            </section>
        </aside>

        <div class="content-wrapper">
            <div id="mapid"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script>$.widget.bridge('uibutton', $.ui.button);</script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script src="dist/js/app.min.js"></script>

<script>
    // Ces variables seront remplies dynamiquement par AJAX et les chargements GeoJSON
    let risquesParQuartier = {};
    let maxRisques = 0;
    let communesQuartiers = {}; // Structure { "Commune1": ["QuartierA", "QuartierB"], ... }

    // Variables pour stocker les données GeoJSON chargées une seule fois
    let geojsonDataCommunes = null;
    let geojsonDataQuartiers = null;

    // Variables pour les couches Leaflet qui seront ajoutées/retirées dynamiquement
    let currentGeoJsonLayer = null; // Pour la couche actuellement affichée (communes ou quartiers)

    // Initialisation de la carte Leaflet
    var carte = L.map('mapid').setView([5.3294815, -3.9919594], 11);

    // Ajout des fonds de carte
    var osm = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(carte);
    var GoogleStreets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'] });
    var GoogleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3'] });
    var fonds = { OpenStreetMap: osm, GoogleMap: GoogleStreets, Satellite: GoogleHybrid };
    L.control.layers(fonds).addTo(carte);
    L.control.scale().addTo(carte);

    // Fonction pour déterminer la couleur du risque
    // Les communes/quartiers sans informations de risque seront coloriés avec '#FFEDA0'
    function getColorForRisk(d) {
        if (maxRisques === 0) { // Si aucun risque n'est enregistré ou filtré, tout est "aucun risque"
            return '#FFEDA0';
        }
        return d > maxRisques * 0.75 ? '#800026' :
               d > maxRisques * 0.50 ? '#BD0026' :
               d > maxRisques * 0.25 ? '#E31A1C' :
               d > 0              ? '#FC4E2A' :
                                    '#FFEDA0'; // Couleur pour "Aucun risque"
    }

    // --- Fonctions de chargement et mise à jour des données ---

    // 1. Fonction pour charger les listes de communes et quartiers (pour les menus déroulants)
    function loadCommunesAndQuartiersDropdowns() {
        $.ajax({
            url: 'ajax.php', // Assurez-vous que cette URL est correcte pour votre ajax.php
            type: 'GET',
            data: { elemid: 'get_communes_quartiers' }, // Nouvelle case à ajouter dans ajax.php si elle n'existe pas
            dataType: 'json',
            success: function(response) {
                // response devrait avoir la structure : { communes: [...], communes_et_quartiers: { "Commune1": [...], ... } }
                if (response && response.communes && response.communes_et_quartiers) {
                    communesQuartiers = response.communes_et_quartiers; // Stocke la structure complète
                    
                    const $communeSelect = $('#commune-select');
                    $communeSelect.empty();
                    $communeSelect.append('<option value="Abidjan" selected>Abidjan</option>'); // Option par défaut pour toute la carte
                    
                    response.communes.forEach(commune => {
                        $communeSelect.append(`<option value="${commune}">${commune}</option>`);
                    });

                    // Initialiser le menu des quartiers en fonction de la sélection initiale "Abidjan"
                    const initialCommune = $communeSelect.val();
                    const $quartierSelect = $('#quartier-select');
                    $quartierSelect.empty().append('<option value="Tous">Tous les quartiers</option>');
                    $quartierSelect.prop('disabled', true); // Désactivé par défaut si "Abidjan" sélectionné

                    // Après le chargement des listes, déclenche la mise à jour de la carte
                    updateMapAndRisks();
                } else {
                    console.error("Structure de réponse inattendue pour loadCommunesAndQuartiersDropdowns:", response);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Erreur AJAX lors du chargement des communes et quartiers pour les dropdowns: " + textStatus, errorThrown);
            }
        });
    }

    // 2. Fonction pour charger les données GeoJSON des communes et des quartiers (une seule fois)
    function loadInitialGeoJSONData() {
        $.getJSON("data/communes_abidjan.geojson")
            .done(function(geojson) {
                geojsonDataCommunes = geojson;
                // Si les deux GeoJSON sont chargés, on peut lancer la première mise à jour complète de la carte
                if (geojsonDataQuartiers) {
                    updateMapAndRisks(); 
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Erreur lors du chargement de data/communes_abidjan.geojson : " + textStatus, errorThrown);
            });

        $.getJSON("data/quartiers_abidjan.geojson")
            .done(function(geojson) {
                geojsonDataQuartiers = geojson;
                // Si les deux GeoJSON sont chargés, on peut lancer la première mise à jour complète de la carte
                if (geojsonDataCommunes) {
                    updateMapAndRisks();
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Erreur lors du chargement de data/quartiers_abidjan.geojson : " + textStatus, errorThrown);
            });
    }

    // 3. Fonction principale pour mettre à jour la carte et les données de risques
    function updateMapAndRisks() {
        const selectedCommune = $('#commune-select').val();
        const selectedQuartier = $('#quartier-select').val();
        const selectedRisque = $('#risque-select').val();

        // Récupérer les statistiques de risque du serveur via AJAX
        $.ajax({
            url: 'ajax.php',
            type: 'GET',
            data: { 
                elemid: 'get_risks_by_quartier', 
                commune: selectedCommune,
                quartier: selectedQuartier,
                risque: selectedRisque
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    risquesParQuartier = response.data; // Ceci est un objet { "quartier_name": { total: N, par_type: { ... } }, ... }
                    maxRisques = response.max_risques;
                    drawMapLayers(); // Dessiner les couches de la carte avec les nouvelles données de risque
                    updateLegend(); // Mettre à jour la légende
                } else {
                    console.warn("Aucune donnée de risque ou erreur signalée : " + (response.error || ""));
                    risquesParQuartier = {}; // Réinitialise si pas de données ou erreur
                    maxRisques = 0;
                    drawMapLayers(); // Dessine la carte même sans données de risque
                    updateLegend();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Erreur AJAX pour les statistiques de risque : " + textStatus, errorThrown);
                risquesParQuartier = {}; // Réinitialise sur erreur réseau
                maxRisques = 0;
                drawMapLayers(); // Dessine la carte même en cas d'erreur AJAX
                updateLegend();
            }
        });
    }

    // 4. Fonction pour dessiner les couches Leaflet (appelée après chargement des données)
    function drawMapLayers() {
        // Supprimer la couche actuellement affichée
        if (currentGeoJsonLayer) {
            carte.removeLayer(currentGeoJsonLayer);
        }

        const selectedCommune = $('#commune-select').val();
        const selectedQuartier = $('#quartier-select').val();
        const selectedRisque = $('#risque-select').val(); // Pas directement utilisé ici pour le style global

        let featuresToDraw = [];
        let onEachFeatureFunction;
        let styleFunction;

        if (selectedCommune === 'Abidjan') {
            // Afficher toutes les communes
            if (!geojsonDataCommunes) {
                console.warn("geojsonDataCommunes non chargé. Impossible de dessiner les communes.");
                return;
            }
            featuresToDraw = geojsonDataCommunes.features;

            styleFunction = function(feature) {
                // Style de base pour les contours de communes
                return {
                    fillColor: '#FFEDA0', // Couleur par défaut (aucun risque)
                    weight: 1,
                    opacity: 1,
                    color: 'white',
                    dashArray: '3',
                    fillOpacity: 0.4
                };
            };

            onEachFeatureFunction = function(feature, layer) {
                layer.bindPopup(`<b>Commune:</b> ${feature.properties.commune}`);
                layer.on('click', function(e) {
                    carte.fitBounds(e.target.getBounds());
                });
            };

            currentGeoJsonLayer = L.geoJson(featuresToDraw, {
                style: styleFunction,
                onEachFeature: onEachFeatureFunction
            }).addTo(carte);
            
            if (currentGeoJsonLayer.getLayers().length > 0) {
                carte.fitBounds(currentGeoJsonLayer.getBounds());
            }

        } else {
            // Afficher les quartiers d'une commune spécifique
            if (!geojsonDataQuartiers) {
                console.warn("geojsonDataQuartiers non chargé. Impossible de dessiner les quartiers.");
                return;
            }
            featuresToDraw = geojsonDataQuartiers.features.filter(f => f.properties.commune === selectedCommune);
            if (selectedQuartier !== 'Tous') {
                featuresToDraw = featuresToDraw.filter(f => f.properties.nom_quartier === selectedQuartier);
            }

            styleFunction = function(feature) {
                const nom_quartier = feature.properties.nom_quartier;
                let riskCount = 0;
                if (risquesParQuartier[nom_quartier]) {
                    const currentSelectedRisque = $('#risque-select').val();
                    if (currentSelectedRisque === 'Total') {
                        riskCount = risquesParQuartier[nom_quartier].total;
                    } else {
                        riskCount = risquesParQuartier[nom_quartier].par_type[currentSelectedRisque] || 0;
                    }
                }
                
                const fillColor = getColorForRisk(riskCount);
                // Si le risque est 0 ou si c'est un quartier sans info, on le rend un peu transparent
                const fillOpacity = (riskCount > 0 || selectedRisque === 'Total') ? 0.7 : 0.4; 

                return {
                    fillColor: fillColor,
                    weight: 1,
                    opacity: 1,
                    color: 'white',
                    dashArray: '3',
                    fillOpacity: fillOpacity
                };
            };

            onEachFeatureFunction = function(feature, layer) {
                const nom_quartier = feature.properties.nom_quartier;
                const riskData = risquesParQuartier[nom_quartier] || { total: 0, par_type: {} }; 
                const currentSelectedRisque = $('#risque-select').val();
                
                let popupContent = `<b>Quartier:</b> ${nom_quartier}<br>`;
                if (currentSelectedRisque === 'Total') {
                    popupContent += `<b>Nombre total de risques:</b> ${riskData.total}`;
                } else {
                    const count = riskData.par_type[currentSelectedRisque] || 0;
                    popupContent += `<b>Nombre de ${currentSelectedRisque}:</b> ${count}`;
                }
                layer.bindPopup(popupContent);

                // Ajouter l'événement de clic pour zoomer sur le quartier
                layer.on('click', function(e) {
                    carte.fitBounds(e.target.getBounds());
                });
            };

            currentGeoJsonLayer = L.geoJson(featuresToDraw, {
                style: styleFunction,
                onEachFeature: onEachFeatureFunction
            }).addTo(carte);

            if (currentGeoJsonLayer.getLayers().length > 0) {
                carte.fitBounds(currentGeoJsonLayer.getBounds());
            }
        }
    }

    // --- Gestionnaires d'événements pour les filtres ---
    $('#commune-select').on('change', function() {
        const commune = $(this).val();
        const $quartierSelect = $('#quartier-select');
        $quartierSelect.empty().append('<option value="Tous">Tous les quartiers</option>');

        if (commune !== 'Abidjan') {
            $quartierSelect.prop('disabled', false);
            const quartiers = communesQuartiers[commune];
            if (quartiers) {
                quartiers.forEach(function(quartier) {
                    $quartierSelect.append(`<option value="${quartier}">${quartier}</option>`);
                });
            }
        } else {
            $quartierSelect.prop('disabled', true);
        }
        updateMapAndRisks(); // Déclenche la mise à jour complète (données et carte)
    });

    $('#quartier-select').on('change', function() {
        updateMapAndRisks(); // Déclenche la mise à jour complète
    });

    $('#risque-select').on('change', function() {
        updateMapAndRisks(); // Déclenche la mise à jour complète
    });
    
    // --- Gestion de la légende ---
    let currentLegend = null; // Référence à l'objet de légende actuel
    function updateLegend() {
        if (currentLegend) {
            carte.removeControl(currentLegend); // Retire l'ancienne légende
        }
        
        currentLegend = L.control({position: 'bottomright'});
        currentLegend.onAdd = function (map) {
            var div = L.DomUtil.create('div', 'info legend');
            // Les grades de la légende doivent être basés sur le maxRisques actuel
            const grades = [0, maxRisques * 0.25, maxRisques * 0.50, maxRisques * 0.75];
            const labels = [];
            
            div.innerHTML += '<h4>Niveau de Risque</h4>';
            
            if (maxRisques === 0) {
                 labels.push('<i style="background:' + getColorForRisk(0) + '"></i> Aucun risque');
            } else {
                 labels.push('<i style="background:' + getColorForRisk(0) + '"></i> Aucun');
                 // Utilise Math.round pour éviter les décimales pour les grades
                 labels.push('<i style="background:' + getColorForRisk(grades[0] + 1) + '"></i> ' + `${Math.round(grades[0] + 1)} - ${Math.round(grades[1])}`);
                 labels.push('<i style="background:' + getColorForRisk(grades[1] + 1) + '"></i> ' + `${Math.round(grades[1] + 1)} - ${Math.round(grades[2])}`);
                 labels.push('<i style="background:' + getColorForRisk(grades[2] + 1) + '"></i> ' + `${Math.round(grades[2] + 1)} - ${Math.round(grades[3])}`);
                 labels.push('<i style="background:' + getColorForRisk(maxRisques) + '"></i> ' + `${Math.round(grades[3] + 1)} +`);
            }
            div.innerHTML += labels.join('<br>');
            return div;
        };
        currentLegend.addTo(carte);
    }
    
    // --- Initialisation au chargement de la page ---
    $(document).ready(function() {
        // Chargement initial des fichiers GeoJSON
        loadInitialGeoJSONData(); 
        // Chargement initial des données pour les menus déroulants (qui déclenchera la première updateMapAndRisks)
        loadCommunesAndQuartiersDropdowns();
        // updateLegend() sera appelée à la fin de updateMapAndRisks()
    });

</script>
</body>
</html>