<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

$conn = new PDO(DB_DSN, DB_USER, DB_PASS);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Options du select Commune (variable différente de $liste_commune réservée à la sidebar)
$commune_options = '';
$result = $conn->query('SELECT id, commune as nom_commune FROM public.communes_abidjan ORDER BY commune');
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $commune_options .= '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['nom_commune']) . '</option>';
}

// Pré-remplir si édition via GET
$initial_latitude  = '';
$initial_longitude = '';
$initial_date      = '';
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt_load = $conn->prepare('SELECT latitude, longitude, date FROM public.informations WHERE id = :id');
    $stmt_load->execute([':id' => (int)$_GET['id']]);
    $row = $stmt_load->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $initial_latitude  = htmlspecialchars($row['latitude']);
        $initial_longitude = htmlspecialchars($row['longitude']);
        $initial_date      = !empty($row['date']) ? date('Y-m-d', strtotime($row['date'])) : '';
    }
}

// Traitement du formulaire
$success_message = '';
$error_message   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commune     = $_POST['commune']     ?? '';
    $quartier    = $_POST['quartier']    ?? '';
    $risques     = $_POST['risque']      ?? '';
    $description = $_POST['description'] ?? '';
    $date        = !empty($_POST['date']) ? $_POST['date'] : null;
    $longitude   = $_POST['longitude']   ?? '';
    $latitude    = $_POST['latitude']    ?? '';

    $types_valides = ['Inondation', 'Erosion', 'Eboulement', 'Effondrement'];
    if (!in_array($risques, $types_valides)) {
        $error_message = 'Veuillez sélectionner un type de risque valide.';
    } elseif (empty($_FILES['file']['name'])) {
        $error_message = 'Veuillez joindre un fichier.';
    } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Erreur lors du téléchargement du fichier : ' . $_FILES['file']['error'];
    } else {
        $targetDir = __DIR__ . '/informations/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $file = basename($_FILES['file']['name']);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetDir . $file)) {
            $dateExpr = $date ? ':date' : 'NOW()';
            $stmt = $conn->prepare("
                INSERT INTO informations (commune, quartier, risques, description, date, fichier, longitude, latitude)
                VALUES (:commune, :quartier, :risques, :description, $dateExpr, :fichier, :longitude, :latitude)
            ");
            $stmt->bindParam(':commune',     $commune);
            $stmt->bindParam(':quartier',    $quartier);
            $stmt->bindParam(':risques',     $risques);
            $stmt->bindParam(':description', $description);
            if ($date) $stmt->bindValue(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':fichier',     $file);
            $stmt->bindParam(':longitude',   $longitude);
            $stmt->bindParam(':latitude',    $latitude);
            if ($stmt->execute()) {
                $success_message = 'Alerte enregistrée avec succès.';
            } else {
                $error_message = "Erreur lors de l'insertion des données.";
            }
        } else {
            $error_message = 'Erreur lors du déplacement du fichier.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Émettre une alerte</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">
  <style>
    #mapid {
      width: 100%;
      height: 450px;
    }
    .form-map-wrapper {
      display: flex;
      gap: 20px;
      align-items: flex-start;
    }
    .form-map-wrapper form {
      flex: 0 0 340px;
    }
    .form-map-wrapper #mapid {
      flex: 1;
    }
    @media (max-width: 768px) {
      .form-map-wrapper { flex-direction: column; }
      .form-map-wrapper form { flex: none; width: 100%; }
    }
  </style>
</head>
<body class="skin-blue sidebar-mini">
<div class="wrapper">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><i class="fa fa-exclamation-triangle"></i> Émettre une alerte</h1>
      <small>Signalez un risque naturel dans votre zone</small>
    </section>

    <section class="content">
      <div class="box box-primary">
        <div class="box-body">

          <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
          <?php endif; ?>
          <?php if ($error_message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
          <?php endif; ?>

          <div class="form-map-wrapper">
            <form action="index.php" method="post" enctype="multipart/form-data">

              <div class="form-group">
                <label for="commune">Commune :</label>
                <select class="form-control" id="commune" name="commune" onchange="zoomCommune(this.value);" required>
                  <option value="">Choisissez votre commune</option>
                  <?php echo $commune_options; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="quartier">Quartier :</label>
                <input type="text" class="form-control" name="quartier" id="quartier" placeholder="Quartier">
              </div>

              <div class="form-group">
                <label for="risque">Type de risque :</label>
                <select class="form-control" name="risque" id="risque" required>
                  <option value="">Choisissez un type</option>
                  <option value="Inondation">Inondation</option>
                  <option value="Erosion">Erosion</option>
                  <option value="Eboulement">Eboulement</option>
                  <option value="Effondrement">Effondrement</option>
                </select>
              </div>

              <div class="form-group">
                <label for="date">Date de l'événement :</label>
                <input type="date" class="form-control" name="date" id="date" value="<?= htmlspecialchars($initial_date) ?>">
              </div>

              <div class="form-group">
                <label for="description">Description :</label>
                <textarea class="form-control" name="description" id="description" rows="3"></textarea>
              </div>

              <div class="form-group">
                <label for="file">Photo ou vidéo :</label>
                <input type="file" class="form-control" id="file" name="file" required>
              </div>

              <div class="form-group">
                <label for="lat">Latitude :</label>
                <input type="text" class="form-control" name="latitude" id="lat" readonly value="<?= htmlspecialchars($initial_latitude) ?>">
              </div>

              <div class="form-group">
                <label for="lon">Longitude :</label>
                <input type="text" class="form-control" name="longitude" id="lon" readonly value="<?= htmlspecialchars($initial_longitude) ?>">
              </div>

              <button type="submit" class="btn btn-primary">Envoyer</button>
              <a href="../historiques/index.php" class="btn btn-default">Voir l'historique</a>
            </form>

            <div id="mapid"></div>
          </div>

        </div>
      </div>
    </section>
  </div><!-- /.content-wrapper -->

</div><!-- /.wrapper -->

<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
  var carte = L.map('mapid', { center: [5.3294815, -3.9919594], zoom: 11 });
  var marqueur = null;

  var limite_commune = L.geoJson(null, {
    style: function() { return { color:'black', weight:2, fill:true, fillColor:'rgba(255,255,255,0.5)', fillOpacity:0.5 }; }
  }).addTo(carte);

  var osm = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(carte);

  var communes_abidjan = L.geoJson(null, {
    style: function() { return { color:'black', weight:2, fill:true, fillColor:'rgba(255,255,255,0.5)', fillOpacity:0.4 }; }
  }).addTo(carte);

  L.control.layers({ OpenStreetMap: osm }, { 'Limite Abidjan': communes_abidjan }).addTo(carte);
  L.control.scale().addTo(carte);

  carte.on('click', function(e) {
    carte.closePopup();
    if (marqueur) carte.removeLayer(marqueur);
    var lat = e.latlng.lat.toFixed(6);
    var lon = e.latlng.lng.toFixed(6);
    marqueur = L.marker([lat, lon], { draggable: true }).addTo(carte);
    document.querySelector('#lat').value = lat;
    document.querySelector('#lon').value = lon;
    fetchCommuneFromCoords(lat, lon);
    marqueur.bindPopup('Lat: ' + lat + '<br>Lon: ' + lon).openPopup();
    marqueur.on('dragend', function(ev) {
      var ll = ev.target.getLatLng();
      document.querySelector('#lat').value = ll.lat.toFixed(6);
      document.querySelector('#lon').value = ll.lng.toFixed(6);
      marqueur.getPopup().setContent('Lat: ' + ll.lat.toFixed(6) + '<br>Lon: ' + ll.lng.toFixed(6));
      fetchCommuneFromCoords(ll.lat.toFixed(6), ll.lng.toFixed(6));
    });
  });

  function zoomCommune(id_commune) {
    limite_commune.clearLayers();
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        limite_commune.addData(JSON.parse(xhr.responseText));
        limite_commune.addTo(carte);
      }
    };
    xhr.open('GET', 'ajax.php?elemid=zoom_commune&id_commune=' + id_commune, true);
    xhr.send(null);
  }

  function fetchCommuneFromCoords(lat, lon) {
    fetch('api_geocode.php?lat=' + lat + '&lon=' + lon)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success && data.commune) {
          document.getElementById('commune').value = data.commune.id;
          zoomCommune(data.commune.id);
          if (data.quartier) document.getElementById('quartier').value = data.quartier;
        }
      })
      .catch(function(err) { console.warn('Géocodage inversé impossible:', err); });
  }

  $.getJSON('../data/communes_abidjan.json', function(donnee) {
    communes_abidjan.addData(donnee);
    communes_abidjan.addTo(carte);
  });

  $(document).ready(function() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        function(pos) {
          var lat = pos.coords.latitude.toFixed(6);
          var lon = pos.coords.longitude.toFixed(6);
          document.getElementById('lat').value = lat;
          document.getElementById('lon').value = lon;
          if (marqueur) carte.removeLayer(marqueur);
          marqueur = L.marker([pos.coords.latitude, pos.coords.longitude], { draggable: true }).addTo(carte);
          marqueur.bindPopup('Lat: ' + lat + '<br>Lon: ' + lon).openPopup();
          carte.setView([pos.coords.latitude, pos.coords.longitude], 15);
          fetchCommuneFromCoords(lat, lon);
        },
        function() {
          var initLat = document.querySelector('#lat').value;
          var initLon = document.querySelector('#lon').value;
          if (initLat && initLon) {
            marqueur = L.marker([parseFloat(initLat), parseFloat(initLon)], { draggable: true }).addTo(carte);
            carte.setView([parseFloat(initLat), parseFloat(initLon)], 15);
          }
        },
        { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
      );
    }
    var initCommune = document.getElementById('commune').value;
    if (initCommune) zoomCommune(initCommune);
  });
</script>

<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<script>$.widget.bridge('uibutton', $.ui.button);</script>
</body>
</html>
