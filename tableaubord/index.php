<?php
require_once __DIR__ . '/../config.php';
// connection à la base données
  function connect() {
    $conn = null;

    try{
      $conn = new PDO(DB_DSN, DB_USER, DB_PASS);
     // Ajout : pour une meilleure gestion des erreurs PDO
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      // echo "Connexion à la base de données réussie !";
    } 
    // S'il existe un problème de connection, on obtient le message d'erreur
    catch(PDOException $ex) {
      echo 'echec de la connexion à la base de données : : ' . $ex->getMessage(); 
    }
    return $conn;
  }
$conn=connect();
// Vérifier si la connexion a réellement réussi (utile si connect() n'avait pas d'exit)
if (!$conn) {
    exit();
}
 $liste_commune=""; // Initialiser la variable pour la liste deroulante
  // $liste_quartier=""; // Initialiser la variable pour la liste deroulante
   $count_Inondation= 0;// Initialiser la variable pour le compteur
   $count_Erosion= 0; // Initialiser la variable pour le compteur
   $count_Eboulement= 0; // Initialiser la variable pour le compteur
   $count_Effondrement= 0;// Initialiser la variable pour le compteur
   $count_risques= 0;// Initialiser la variable pour le compteur
// Définition de la requête SQL pour les communes
 $sql = 'SELECT id, "commune" as nom_commune  FROM public.communes_abidjan';
try {
//Executer la requete
   $result = $conn->query($sql);
    //Parcourir la table des résultats de la requête
while($row = $result->fetch(PDO::FETCH_ASSOC)){
    //affiche le contenu de la table
   $liste_commune.= '<li><a href="#" onclick="zoomCommune(\''.$row['id'].'\');">'.$row['nom_commune'].'</a></li>';
	}  
     $result->closeCursor(); // Libérer les ressources
} catch(PDOException $ex) {
    error_log('Connection error : ' . $ex->getMessage());
    echo '<p style="color:red;">Erreur de connexion à la base de données. Veuillez réessayer plus tard.</p>'; // <--- Premier endroit possible
    exit();
}
//Définition de la requête SQL
$sql_risques = "SELECT count(*) AS total_risque FROM informations WHERE risques IN ('Inondation', 'Erosion', 'Eboulement', 'Effondrement')";
//Executer la requete
$result_risques = $conn->query($sql_risques);
//Parcourir la table des résultats de la requête
$row_risques = $result_risques->fetch(PDO::FETCH_ASSOC);
$total_risque = $row_risques['total_risque'];

//Définition de la requête SQL
$sql_Inondation = "SELECT count(*) AS total_inondation FROM informations WHERE risques = 'Inondation'";
  //Executer la requete
  $result_Inondation = $conn->query($sql_Inondation);
  //Parcourir la table des résultats de la requête
  $row_Inondation = $result_Inondation->fetch(PDO::FETCH_ASSOC);
  $total_inondation = $row_Inondation['total_inondation'];

//Définition de la requête SQL
$sql_Erosion = "SELECT count(*) AS total_erosion FROM informations WHERE risques = 'Erosion'";
//Executer la requete
$result_Erosion = $conn->query($sql_Erosion);
//Parcourir la table des résultats de la requête
$row_Erosion = $result_Erosion->fetch(PDO::FETCH_ASSOC);
$total_erosion = $row_Erosion['total_erosion'];

//Définition de la requête SQL
$sql_Eboulement = "SELECT count(*) AS total_eboulement FROM informations WHERE risques = 'Eboulement'";
//Executer la requete
$result_Eboulement = $conn->query($sql_Eboulement);
//Parcourir la table des résultats de la requête
$row_Eboulement = $result_Eboulement->fetch(PDO::FETCH_ASSOC);
$total_eboulement = $row_Eboulement['total_eboulement'];

//Définition de la requête SQL
$sql_Effondrement = "SELECT count(*) AS total_effondrement FROM informations WHERE risques = 'Effondrement'";
//Executer la requete
$result_Effondrement = $conn->query($sql_Effondrement);
//Parcourir la table des résultats de la requête
$row_Effondrement = $result_Effondrement->fetch(PDO::FETCH_ASSOC);
$total_effondrement = $row_Effondrement['total_effondrement'];

$sql_traite = "SELECT count(*) AS total_traite FROM informations WHERE new_statut = 'traite'";
$result_traite = $conn->query($sql_traite);
$row_traite = $result_traite->fetch(PDO::FETCH_ASSOC);
$total_traite = $row_traite['total_traite'];

$sql_non_traite = "SELECT count(*) AS total_non_traite FROM informations WHERE new_statut = 'non_traite'";
$result_non_traite = $conn->query($sql_non_traite);
$row_non_traite = $result_non_traite->fetch(PDO::FETCH_ASSOC);
$total_non_traite = $row_non_traite['total_non_traite'];

$sql_communes = "SELECT count(DISTINCT commune) AS total_communes FROM informations";
$result_communes = $conn->query($sql_communes);
$row_communes = $result_communes->fetch(PDO::FETCH_ASSOC);
$total_communes = $row_communes['total_communes'];

// Barres : traités vs non traités par type de risque
$sql_bar = "SELECT risques,
    SUM(CASE WHEN new_statut = 'traite'     THEN 1 ELSE 0 END) AS traite,
    SUM(CASE WHEN new_statut = 'non_traite' THEN 1 ELSE 0 END) AS non_traite
FROM informations
WHERE risques IN ('Inondation','Erosion','Eboulement','Effondrement')
GROUP BY risques
ORDER BY risques";
$bar_data = ['labels'=>[], 'traite'=>[], 'non_traite'=>[]];
foreach ($conn->query($sql_bar) as $r) {
    $bar_data['labels'][]     = $r['risques'];
    $bar_data['traite'][]     = (int)$r['traite'];
    $bar_data['non_traite'][] = (int)$r['non_traite'];
}
$bar_json = json_encode($bar_data);

// Courbe : évolution mensuelle des signalements (12 derniers mois)
$sql_line = "SELECT TO_CHAR(date::date, 'Mon YYYY') AS mois,
                    TO_CHAR(date::date, 'YYYY-MM')  AS mois_sort,
                    count(*) AS total
FROM informations
WHERE date IS NOT NULL
GROUP BY TO_CHAR(date::date, 'YYYY-MM'), TO_CHAR(date::date, 'Mon YYYY')
ORDER BY TO_CHAR(date::date, 'YYYY-MM')
LIMIT 12";
$line_data = ['labels'=>[], 'totals'=>[]];
foreach ($conn->query($sql_line) as $r) {
    $line_data['labels'][] = $r['mois'];
    $line_data['totals'][] = (int)$r['total'];
}
$line_json = json_encode($line_data);
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Inondation à cocody </title>
    <!--liens de leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
		<script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-groupedlayercontrol/0.6.1/leaflet.groupedlayercontrol.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Tell the browser to be responsive to screen width/Dites au navigateur d'être réactif à la largeur de l'écran -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
/*contenant du navigateur du titre*/
#contenu {
      text-align: center;
    }
      .sidebar-form {
        padding: 10px 0;
      }
      /*contenant du navigateur du titre des type de risque*/
.small-box {
    border-radius: 6px;
    overflow: hidden;
}
.small-box:hover {
    box-shadow: inset 0 0 0 2000px rgba(0,0,0,0.08);
    cursor: pointer;
}
.small-box .inner {
    padding: 12px 14px 10px;
}
.small-box h3 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 4px;
}
.small-box p {
    font-size: 13px;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.small-box .icon {
    font-size: 50px;
    top: 10px;
}
.carte-container {
    display: flex; /* Utilise Flexbox pour organiser les enfants */
    height: 100vh; /* La hauteur du conteneur est égale à la hauteur de la fenêtre */
    width: 100%;
}
      /*la carte*/
      #mapid {
    flex-grow: 1; /* Permet à la carte de prendre tout l'espace restant */
        width: 100%; /*largueur de l'image*/
      /*  height:580px; /*hauteur de l'image*/
        border : 0px solid green;
      /*  margin: auto;
         float: right;*/
        }
        /* KPI cards — s'étirent pour remplir toute la largeur */
        @media (min-width: 992px) {
            .kpi-col { flex: 1 1 0; width: auto !important; }
            .kpi-col .small-box { height: 100%; }
        }
        /* Statistiques des risques — déplacée en haut de page */
        #statistiques {
            background: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 16px 20px 20px;
            margin-bottom: 18px;
            text-align: center;
        }
        #statistiques h3 {
            margin-top: 0;
            font-size: 15px;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #3c8dbc;
            padding-bottom: 8px;
            margin-bottom: 14px;
            text-align: left;
        }
        .skip-to-stats {
            display: inline-block;
            margin-bottom: 12px;
            padding: 5px 14px;
            background: #3c8dbc;
            color: #fff !important;
            border-radius: 3px;
            font-size: 12px;
            text-decoration: none;
        }
        .skip-to-stats:hover { background: #367fa9; }
        /* Ancienne barre latérale carte — masquée */
        #sidebar { display: none; }

        /* Graphiques statistiques */
#statistiques canvas {
    max-height: 260px;
}
/*la legende*/
        .ocsControl leaflet-control,{
        background:
        padding: 5px;
        border-radius: 5px;
      }
    /*logo*/
        .modal img {
        max-width: 100%;
        height: auto;
      }
/* Style pour le clignotement du popup */
.blinking-red {
    animation: blink-animation 1s infinite alternate; /* Fait clignoter le marqueur */
    background-color: red !important; /* Force la couleur rouge */
}
@keyframes blink-animation {
    from { opacity: 1; }
    to { opacity: 0.5; }
}
/* Styles pour la modale */
#riskDetailsModal .modal-body {
    max-height: 70vh; /* Limite la hauteur du corps de la modale pour le défilement */
    overflow-y: auto; /* Active le défilement si le contenu dépasse */
}
#riskDetailsModal img {
    max-width: 100%; /* S'assure que l'image ne dépasse pas la largeur de la modale */
    height: auto;
    display: block;
    margin-top: 10px;
}

		</style>
  </head>
<body class="skin-blue sidebar-mini">
<div class="wrapper">

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

      <!-- Content Wrapper -->
      <div class="content-wrapper">
      <!-- Main content -->
      <section class="content-header">
        <h1><i class="fa fa-table"></i> Tableau de bord</h1>
        <a href="#statistiques" class="skip-to-stats"><i class="fa fa-bar-chart"></i> Statistiques des risques &darr;</a>
      </section>
      <section class="content">
        <!-- Statistiques des risques -->
        <div id="statistiques">
          <h3><i class="fa fa-bar-chart"></i> Statistiques des risques</h3>
          <div class="row">
            <div class="col-xs-12 col-md-4">
              <p style="text-align:center;font-size:12px;color:#666;margin-bottom:6px;">Répartition par type</p>
              <canvas id="risksPieChart"></canvas>
            </div>
            <div class="col-xs-12 col-md-4">
              <p style="text-align:center;font-size:12px;color:#666;margin-bottom:6px;">Traités vs Non traités</p>
              <canvas id="risksBarChart"></canvas>
            </div>
            <div class="col-xs-12 col-md-4">
              <p style="text-align:center;font-size:12px;color:#666;margin-bottom:6px;">Évolution mensuelle</p>
              <canvas id="risksLineChart"></canvas>
            </div>
          </div>
        </div>
        <!-- Stat boxes -->
        <div class="row" style="display:flex;flex-wrap:wrap;">
          <div class="col-xs-6 col-sm-4 col-md-2 kpi-col">
            <div class="small-box bg-red">
              <div class="inner">
                <h3 id="nombre_risque"><?php echo $total_risque; ?></h3>
                <p>Zones impactées</p>
              </div>
              <div class="icon"><i class="fa fa-map-marker"></i></div>
            </div>
          </div>
          <div class="col-xs-6 col-sm-4 col-md-2 kpi-col">
            <div class="small-box" style="background:#36A2EB;color:#fff;">
              <div class="inner">
                <h3 id="nombre_inondation" style="color:#fff;"><?php echo $total_inondation; ?></h3>
                <p>Inondations</p>
              </div>
              <div class="icon"><i class="fa fa-tint"></i></div>
            </div>
          </div>
          <div class="col-xs-6 col-sm-4 col-md-2 kpi-col">
            <div class="small-box" style="background:#FFCE56;color:#333;">
              <div class="inner">
                <h3 id="nombre_erosion" style="color:#333;"><?php echo $total_erosion; ?></h3>
                <p>Érosions</p>
              </div>
              <div class="icon" style="color:rgba(0,0,0,0.15);"><i class="fa fa-exclamation-triangle"></i></div>
            </div>
          </div>
          <div class="col-xs-6 col-sm-4 col-md-2 kpi-col">
            <div class="small-box" style="background:#4BC0C0;color:#fff;">
              <div class="inner">
                <h3 id="nombre_eboulement" style="color:#fff;"><?php echo $total_eboulement; ?></h3>
                <p>Éboulements</p>
              </div>
              <div class="icon"><i class="fa fa-warning"></i></div>
            </div>
          </div>
          <div class="col-xs-6 col-sm-4 col-md-2 kpi-col">
            <div class="small-box" style="background:#FF6384;color:#fff;">
              <div class="inner">
                <h3 id="nombre_effondrement" style="color:#fff;"><?php echo $total_effondrement; ?></h3>
                <p>Effondrements</p>
              </div>
              <div class="icon"><i class="fa fa-home"></i></div>
            </div>
          </div>
        </div><!-- /.row -->

        <div class="carte-container">
     <!--Importer le cadre de la carte-->
		<div id="mapid"></div>
    <div class= "ocsControl leaflet-control"> 
      <div class= "counterBoxOcs"> 
        <div class="counterBoxOcsContent" style ="background: #F5F5DC;"></div>
      </div>
      <div class= "counterBoxOcs"> 
        <div class="counterBoxOcsContent" style ="background: #0000FF;"></div>
      </div>
    </div>
 </div> 

</div>
        
    </section>

<!--div class="col-xl-3 col-md-6">                                                
    <div class="card" style="text-align:center">                                                    
        <div class="card-header" style="padding: 25px 20px 5px;">                                                       
        <h5 style="text-align:center">Repartition des Zones </h5>                                                       
        <span>Par catégorie</span>                                                  
        </div>                                                  
        <div class="card-block">                                                        
        <canvas id="polarChart" width="350" height="500px"></canvas>
        </div>													
        <button class="btn bg-c-green btn-block p-t-15 p-b-15 text-white" style="background : #2f3640"><?php echo $titre; ?></button>                                                
    </div>                                                                                              												                                         
</div-->


 <div class="modal fade" id="riskDetailsModal" tabindex="-1" role="dialog" aria-labelledby="riskDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="riskDetailsModalLabel">Détails de l'incident</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="riskInfoContent">
            </div>
          <hr>
          <h4>Recommandation et Statut</h4>
          <form id="recommendationForm">
            <input type="hidden" id="modalRiskId" name="id">
            <div class="form-group">
              <label for="recommandation">Écrivez votre recommandation :</label>
              <textarea class="form-control" id="recommandationText" name="recommandation" rows="4" placeholder="Saisissez vos recommandations ici..."></textarea>
            </div>
            <div class="form-group">
              <label for="riskStatut">Statut de l'incident :</label>
              <select class="form-control" id="riskStatut" name="new_statut" required>
                <option value="">-- Sélectionner un statut --</option>
                <option value="non_traite">Non Traité (Rouge clignotant)</option>
                <option value="traite">Traité (Couleur dépend du risque)</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary">Valider la Recommandation</button>
            <div id="recommendationMessage" class="mt-2 text-center"></div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        </div>
      </div>
    </div>
  </div>

    <script>
    // Initialiser la carte
    var carte = L.map('mapid', {
        center: [5.3294815, -3.9919594],
        zoom: 13
    });

    // Définition des couches de groupe pour chaque type de risque (globale)
    var inondationLayer = L.layerGroup();
    var erosionLayer = L.layerGroup();
    var eboulementLayer = L.layerGroup();
    var effondrementLayer = L.layerGroup();
    // Couche pour les risques généraux (pour la vue initiale et le clignotement)
    var allRisksLayer = L.layerGroup().addTo(carte); // Ajoutée à la carte par défaut
    // Dictionnaire pour stocker les marqueurs par ID (pour le clignotement et la mise à jour)
    var riskMarkers = {}; // Renommé de 'currentMarkers' à 'riskMarkers' pour correspondre à votre code
    // Pour garder une trace des risques déjà notifiés et éviter de les faire clignoter à nouveau
    let notifiedRiskIds = new Set();
      // VARIABLE POUR LE DIAGRAMME CIRCULAIRE
    let risksPieChart; 
    // caracteristique de la couche de limite de commune
    var limite_commune = L.geoJson(null, {
        style: function(feature) {
            return {
                color: 'blue', // Couleur pour la commune zoomée
                weight: 2,
                fill: true,
                fillColor: 'lightblue',
                fillOpacity: 0.6
            }
        }
    });

    // Déclaration globale pour le graphique pour y accéder depuis d'autres fonctions
//let risksChart;

    // Ajouter fond de carte gratuit à notre carte
    var osm = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(carte);
    var GoogleStreets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    });

    var GoogleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    });

    // Grouper les couches
    var fonds = {
        OpenStreetMap: osm,
        GoogleMap: GoogleStreets,
        Satellite: GoogleHybrid
    };
    // Couche pour toutes les communes (chargée depuis le fichier JSON statique)
    var communes_abidjan = L.geoJson(null, {
        style: function(feature) {
            return {
                color: 'black',
                weight: 2,
                fill: true,
                fillColor: 'gray',
                fillOpacity: 0.1
            }
        }
    });
    var donnees = {
        'Limite Abidjan': communes_abidjan,
        'Risques - Inondation': inondationLayer,
        'Risques - Erosion': erosionLayer,
        'Risques - Eboulement': eboulementLayer,
        'Risques - Effondrement': effondrementLayer,
        'Tous les Risques (Initial)': allRisksLayer
    };
    // Création outils à droite en haut
    var layerControl = L.control.layers(fonds, donnees);
    // Ajouter à la carte
    carte.addControl(layerControl);
    // Ajouter l'echelle cartographique
    L.control.scale().addTo(carte);
    // --- Fonctions AJAX et gestion du zoom ---
    // Utilisation de la fonction getXhr() comme précédemment
    function getXhr() {
        var xhr = null;
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            try {
                xhr = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }
        } else {
            alert("Votre navigateur ne supporte pas les objets XMLHTT");
            xhr = false;
        }
        return xhr;
    }
    
    // Données PHP injectées en JS
    var barData  = <?= $bar_json ?>;
    var lineData = <?= $line_json ?>;

    var risksBarChart, risksLineChart;

    function initBarChart() {
        var canvas = document.getElementById('risksBarChart');
        if (!canvas) return;
        risksBarChart = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: barData.labels,
                datasets: [
                    {
                        label: 'Traités',
                        data: barData.traite,
                        backgroundColor: 'rgba(39,174,96,0.8)',
                        borderColor: 'rgba(39,174,96,1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Non traités',
                        data: barData.non_traite,
                        backgroundColor: 'rgba(231,76,60,0.8)',
                        borderColor: 'rgba(231,76,60,1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    function initLineChart() {
        var canvas = document.getElementById('risksLineChart');
        if (!canvas) return;
        risksLineChart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: lineData.labels,
                datasets: [{
                    label: 'Signalements',
                    data: lineData.totals,
                    borderColor: 'rgba(60,141,188,1)',
                    backgroundColor: 'rgba(60,141,188,0.15)',
                    borderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // Fonction pour initialiser le diagramme circulaire
    function initPieChart() {
        var canvasPie = document.getElementById('risksPieChart');
        if (!canvasPie) return;
        const ctx = canvasPie.getContext('2d');
        risksPieChart = new Chart(ctx, {
            type: 'pie', // Type de diagramme
            data: {
                labels: ['Inondation', 'Erosion', 'Eboulement', 'Effondrement'],
                datasets: [{
                    label: 'Répartition des risques',
                    data: [0, 0, 0, 0], // Données initiales à zéro
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Répartition des types de risques'
                    }
                }
            }
        });
    }
    // Fonction pour mettre à jour les données du diagramme circulaire
    function updatePieChart(stats) {
        if (risksPieChart) {
            risksPieChart.data.datasets[0].data = [
                stats.tt_inondation,
                stats.tt_erosion,
                stats.tt_eboulement,
                stats.tt_effondrement
            ];
            risksPieChart.update();
        }
    }
    // Fonction pour déterminer la couleur du marqueur en fonction du statut et du type de risque
    function getMarkerColor(new_statut, typeRisque) {
        if (new_statut === 'non_traite') {
            return "red"; // Rouge pour non traité (qui clignotera si nouveau)
        } else if (new_statut === 'traite') {
            // Après traitement, la couleur dépendra du type de risque initial
            switch(typeRisque) {
                case 'Inondation': return "darkblue";
                case 'Erosion': return "darkorange";
                case 'Eboulement': return "darkcyan";
                case 'Effondrement': return "darkgreen";
                default: return "grey";
            }
        }
        return "red"; // Par défaut si statut non géré ou inconnu
    }
    // Fonction pour ajouter un marqueur de risque à la bonne couche et avec le bon popup
    function addRiskMarker(risk, isNew = false) {
        let lat = risk.latitude;
        let lon = risk.longitude;
        let riskId = risk.id; // En supposant que 'id' est la bonne propriété pour l'ID du risque
        let riskType = risk.risques;
        let riskStatut = risk.new_statut;
        if (lat && lon && riskId) {
            // Supprime l'ancien marqueur s'il existe pour le mettre à jour
            if (riskMarkers[riskId]) {
                // Supprimer de toutes les couches potentielles où il pourrait se trouver
                allRisksLayer.removeLayer(riskMarkers[riskId]);
                inondationLayer.removeLayer(riskMarkers[riskId]);
                erosionLayer.removeLayer(riskMarkers[riskId]);
                eboulementLayer.removeLayer(riskMarkers[riskId]);
                effondrementLayer.removeLayer(riskMarkers[riskId]);
                // Supprimer du dictionnaire
                delete riskMarkers[riskId];
            }
            let markerColor = getMarkerColor(riskStatut, riskType);
            let marker = L.circleMarker([lat, lon], {
                radius: 7,
                color: "#000",
                weight: 1,
                opacity: 1,
                fillOpacity: 0.8,
                fillColor: markerColor,
                id: riskId, // Stocke l'id pour un accès facile
                typeRisque: riskType // Stocke le typeRisque pour un accès facile
            });
            // Ajoute la classe de clignotement si le statut est 'non_traite'
            // Ceci s'applique à la fois aux risques nouvellement détectés et aux risques 'non_traite' existants
            if (riskStatut === 'non_traite') {
                 marker.on('add', function() {
                    if (this.getElement()) { // Vérifie si l'élément existe avant d'ajouter la classe
                        this.getElement().classList.add('blinking-red');
                    }
                });
            }
            // Stocke le marqueur pour référence future
            riskMarkers[riskId] = marker;
            // Lie un événement de clic pour ouvrir la modale détaillée
            marker.on('click', function() {
                showRiskDetailsModal(riskId);
                // Si le marqueur clignotait, on enlève la classe au clic
                if (this.getElement() && this.getElement().classList.contains('blinking-red')) {
                    this.getElement().classList.remove('blinking-red');
                }
            });
            // Ajout du marqueur à la couche "Tous les risques"
            allRisksLayer.addLayer(marker);
            // Ajout aux couches catégorisées pour la visibilité du panneau de contrôle
            switch (risk.risques) {
                case 'Inondation':
                    inondationLayer.addLayer(marker);
                    break;
                case 'Erosion':
                    erosionLayer.addLayer(marker);
                    break;
                case 'Eboulement':
                    eboulementLayer.addLayer(marker);
                    break;
                case 'Effondrement':
                    effondrementLayer.addLayer(marker);
                    break;
                // Ajoutez d'autres cas pour d'autres types de risques
            }
        }
    }
    // Fonction pour afficher la modale avec les détails du risque
    function showRiskDetailsModal(riskId) {
        var url = 'ajax.php?elemid=get_risk_details&id=' + riskId;
        var xhr = getXhr();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success && response.data) {
                        var risk = response.data;
                        var content = `
                            <p><strong>ID Incident :</strong> ${risk.id}</p>
                            <p><strong>Type de Risque :</strong> ${risk.risques}</p>
                            <p><strong>Commune :</strong> ${risk.commune || 'N/A'}</p>
                            <p><strong>Quartier :</strong> ${risk.quartier || 'N/A'}</p>
                            <p><strong>Date :</strong> ${risk.date}</p>
                            <p><strong>Description :</strong> ${risk.description || 'Pas de description.'}</p>
                            <p><strong>Statut actuel :</strong> ${risk.new_statut || 'Non défini'}</p>
                        `;
                        // Afficher l'image/vidéo si disponible
                        if (risk.fichier) {
                            const mediaBasePath = '../contact/informations/';
                            const mediaUrl = mediaBasePath + risk.fichier;
                            const fileExtension = risk.fichier.split('.').pop().toLowerCase();
                            if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                                content += `<p><strong>Média :</strong><br><img src="${mediaUrl}" alt="Média incident" style="max-width: 100%; height: auto;"></p>`;
                            } else if (['mp4', 'webm', 'ogg'].includes(fileExtension)) {
                                content += `<p><strong>Média :</strong><br><video controls width="100%"><source src="${mediaUrl}" type="video/${fileExtension}">Votre navigateur ne supporte pas la vidéo.</video></p>`;
                            } else {
                                content += `<p><strong>Média :</strong><br><a href="${mediaUrl}" target="_blank">Ouvrir le fichier joint (${fileExtension})</a></p>`;
                            }
                        } else {
                            content += `<p><strong>Média :</strong> Aucun média disponible.</p>`;
                        }
                        var zone = (risk.quartier ? risk.quartier + ', ' : '') + (risk.commune || '');
                        var prep = /^[AEIOUÀÂÉÈÊËÎÏÔÙÛÜaeiouyàâéèêëîïôùûü]/i.test(zone) ? "d'" : "de ";
                        var introMap = {
                            'Inondation':   "Forte pluie et risque d'inondation dans la zone " + prep + zone + ".",
                            'Erosion':      "Risque d'érosion signalé dans la zone " + prep + zone + ".",
                            'Eboulement':   "Risque d'éboulement signalé dans la zone " + prep + zone + ".",
                            'Effondrement': "Risque d'effondrement signalé dans la zone " + prep + zone + "."
                        };
                        var intro = introMap[risk.risques] || "Incident signalé dans la zone " + prep + zone + ".";
                        var recoValue = "ALERTE INONDATION\n" + intro + "\nÉvite les routes inondées. Mets-toi en sécurité.\nÉcoute les consignes des autorités.";

                        $('#riskInfoContent').html(content);
                        $('#modalRiskId').val(riskId);
                        $('#recommandationText').val(recoValue);
                        $('#riskStatut').val(risk.new_statut || '');
                        $('#riskDetailsModal').modal('show'); // Affiche la modale
                    } else {
                        alert('Erreur: ' + (response.error || 'Impossible de récupérer les détails du risque.'));
                    }
                } catch (e) {
                    console.error('Erreur de parsing JSON pour les détails du risque:', e);
                    alert('Erreur de communication avec le serveur pour les détails du risque.');
                }
            } else if (xhr.readyState === 4) {
                console.error('Erreur HTTP lors de la récupération des détails du risque:', xhr.status, xhr.responseText);
                alert('Erreur de serveur lors de la récupération des détails du risque.');
            }
        };
        xhr.open("GET", url, true);
        xhr.send(null);
    }

// Fonction pour initialiser le graphique (à appeler au chargement de la page)
function initChart() {
    var canvasChart = document.getElementById('risksChart');
    if (!canvasChart) return;
    const ctx = canvasChart.getContext('2d');
    risksChart = new Chart(ctx, {
        type: 'bar', 
        data: {
            labels: ['Inondation', 'Erosion', 'Eboulement', 'Effondrement'],
            datasets: [{
                label: 'Nombre de risques',
                data: [0, 0, 0, 0], // Données initiales à zéro
                backgroundColor: [
                    'rgba(54, 162, 235, 0.6)', 
                    'rgba(255, 206, 86, 0.6)', 
                    'rgba(75, 192, 192, 0.6)', 
                    'rgba(255, 99, 132, 0.6)'  
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nombre de risques'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false 
                },
                title: {
                    display: true,
                    text: 'Répartition des risques par type'
                }
            }
        }
    });
}
// Fonction pour mettre à jour les données du graphique
function updateChart(stats) {
    if (risksChart) {
        risksChart.data.datasets[0].data = [
            stats.tt_inondation,
            stats.tt_erosion,
            stats.tt_eboulement,
            stats.tt_effondrement
        ];
        risksChart.update();
    }
}

    // Gérer la soumission du formulaire de recommandation
    $(document).on('submit', '#recommendationForm', function(e) {
        e.preventDefault();
        var riskId  = $('#modalRiskId').val();
        var statut  = $('#riskStatut').val();
        var reco    = $('#recommandationText').val();
        $.ajax({
            url: 'ajax.php?elemid=update_risk_status',
            method: 'POST',
            data: { id: riskId, new_statut: statut, recommandation: reco },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#recommendationMessage').html('<div class="alert alert-success">Statut mis à jour ! SMS envoyés : ' + (response.sms_envoyes || 0) + '</div>');
                    var marker = riskMarkers[riskId];
                    if (marker) {
                        var riskType = marker.options.typeRisque;
                        marker.setStyle({ fillColor: getMarkerColor(statut, riskType) });
                        if (marker.getElement()) {
                            marker.getElement().classList.remove('blinking-red');
                        }
                        if (statut === 'traite') notifiedRiskIds.delete(riskId);
                    }
                    setTimeout(function() {
                        $('#riskDetailsModal').modal('hide');
                        $('#recommendationMessage').empty();
                    }, 2000);
                } else {
                    $('#recommendationMessage').html('<div class="alert alert-danger">Erreur : ' + (response.error || 'Échec') + '</div>');
                }
            },
            error: function(xhr) {
                $('#recommendationMessage').html('<div class="alert alert-danger">Erreur serveur (HTTP ' + xhr.status + ') : ' + xhr.responseText + '</div>');
            }
        });
    });
    // Fonction pour charger tous les risques au démarrage de la carte
    function loadAllRisksInitially() {
        var url = 'ajax.php?elemid=get_risks_coordinates&id_commune=all'; // Utilisez 'all' ou une valeur spéciale pour récupérer tous les risques
        var xhr = getXhr();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        console.error('Erreur au chargement initial des risques:', response.error);
                        return;
                    }
                    allRisksLayer.clearLayers(); // Nettoie la couche avant d'ajouter
                    inondationLayer.clearLayers(); // Nettoie les couches catégorisées aussi
                    erosionLayer.clearLayers();
                    eboulementLayer.clearLayers();
                    effondrementLayer.clearLayers();
                    for (const id in riskMarkers) {
                        if (riskMarkers.hasOwnProperty(id)) {
                            delete riskMarkers[id];
                        }
                    }
                    notifiedRiskIds.clear(); // Important : réinitialiser pour la vue "tous"

                   // --- STATISTIQUES GLOBAL ---
                let stats = {
                    tt_inondation: 0,
                    tt_erosion: 0,
                    tt_eboulement: 0,
                    tt_effondrement: 0,
                    tt_traite: 0,
                    tt_non_traite: 0,
                    tt_risques: response.length,
                    communes: new Set()
                };
                response.forEach(function(risk) {
                    addRiskMarker(risk, false);
                    switch (risk.risques) {
                        case 'Inondation':  stats.tt_inondation++;  break;
                        case 'Erosion':     stats.tt_erosion++;     break;
                        case 'Eboulement':  stats.tt_eboulement++;  break;
                        case 'Effondrement':stats.tt_effondrement++;break;
                    }
                    if (risk.new_statut === 'traite')     stats.tt_traite++;
                    if (risk.new_statut === 'non_traite') stats.tt_non_traite++;
                    if (risk.commune) stats.communes.add(risk.commune);
                });

                document.getElementById('nombre_inondation').textContent  = stats.tt_inondation;
                document.getElementById('nombre_erosion').textContent      = stats.tt_erosion;
                document.getElementById('nombre_eboulement').textContent   = stats.tt_eboulement;
                document.getElementById('nombre_effondrement').textContent = stats.tt_effondrement;
                document.getElementById('nombre_risque').textContent       = stats.tt_risques;

                // Mettre à jour le  type de graphique
                 updatePieChart(stats);

                } catch (e) {
                    console.error('Erreur de parsing JSON au chargement initial des risques:', e);
                }
            } else if (xhr.readyState === 4) {
                console.error('Erreur HTTP au chargement initial des risques:', xhr.status, xhr.responseText);
            }
        };
        xhr.open("GET", url, true);
        xhr.send(null);
    }
    // Fonction pour vérifier les nouveaux risques et les faire clignoter
    function checkForNewRisks() {
        var url = 'ajax.php?elemid=get_recent_risks'; // Nouvelle API pour les risques récents
        var xhr = getXhr();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success && response.data) {
                        response.data.forEach(function(risk) {
                            // Assurez-vous que l'ID est bien 'id' comme dans la réponse
                            if (!notifiedRiskIds.has(risk.id)) {
                                console.log("Nouveau risque détecté:", risk.id);
                                addRiskMarker(risk, true); // Ajoute le marqueur avec clignotement (isNew = true)
                                carte.panTo([risk.latitude, risk.longitude]); // Centre la carte sur le nouveau risque
                                notifiedRiskIds.add(risk.id); // Marque comme notifié
                                // Optionnel: Ouvrir le popup ou la modale automatiquement
                            } else {
                                // Si le risque est déjà notifié mais que son statut est toujours 'non_traite',
                                // on s'assure qu'il clignote toujours. La fonction addRiskMarker gère la mise à jour.
                                if (risk.new_statut === 'non_traite' && riskMarkers[risk.id] &&
                                    riskMarkers[risk.id].getElement() &&
                                    !riskMarkers[risk.id].getElement().classList.contains('blinking-red')) {
                                        riskMarkers[risk.id].getElement().classList.add('blinking-red');
                                }
                            }
                        });
                    }
                } catch (e) {
                    console.error('Erreur de parsing JSON pour les nouveaux risques:', e);
                }
            } else if (xhr.readyState === 4) {
                console.error('Erreur HTTP pour les nouveaux risques:', xhr.status, xhr.responseText);
            }
        };
        xhr.open("GET", url, true);
        xhr.send(null);
    }

    // Fonction zoomCommune
    function zoomCommune(id_commune) {
        limite_commune.clearLayers();
        inondationLayer.clearLayers();
        erosionLayer.clearLayers();
        eboulementLayer.clearLayers();
        effondrementLayer.clearLayers();
        allRisksLayer.clearLayers(); // Efface aussi la couche générale pour ne garder que ceux de la commune

        // Réinitialise riskMarkers et notifiedRiskIds pour le changement de commune
        for (const id in riskMarkers) {
            if (riskMarkers.hasOwnProperty(id)) {
                delete riskMarkers[id];
            }
        }
        notifiedRiskIds.clear(); // IMPORTANT : Réinitialiser notifiedRiskIds lors du changement de commune

        // Optionnel: Réinitialiser les compteurs à 0 ou à un état vide avant de charger les nouveaux
        document.getElementById('nombre_risque').textContent = '0';
        document.getElementById('nombre_inondation').textContent = '0';
        document.getElementById('nombre_erosion').textContent = '0';
        document.getElementById('nombre_eboulement').textContent = '0';
        document.getElementById('nombre_effondrement').textContent = '0';

        // liaison avec le fichier ajax
        var url_zoom = 'ajax.php?elemid=zoom_commune&id_commune=' + id_commune;
        var xhr_zoom = getXhr();
        // récupérer la réponse
        xhr_zoom.onreadystatechange = function() {
            if (xhr_zoom.readyState == 4 && xhr_zoom.status === 200) {
                // 2. Vérifier la réponse brute du serveur
                console.log('Réponse brute de zoom_commune pour ID ' + id_commune + ':', xhr_zoom.responseText);
                try {
                    var json_commune = JSON.parse(xhr_zoom.responseText);
                    if (json_commune.error) {
                        console.error('Erreur AJAX signalée par PHP (zoom):', json_commune.error);
                        alert('Erreur lors du zoom: ' + json_commune.error);
                        return; // Arrêter si une erreur est renvoyée par le serveur
                    }

                    if (json_commune && json_commune.features && json_commune.features.length > 0) {
                        // NOUVEAU: Vérification si la géométrie est valide avant de l'ajouter
                        var firstFeature = json_commune.features[0];
                        if (!firstFeature.geometry || firstFeature.geometry.coordinates.length === 0) {
                            console.warn('La géométrie de la première fonctionnalité est vide ou manquante pour ID ' + id_commune);
                            alert('La géométrie de la commune est vide ou invalide.');
                            return;
                        }

                        // 4. Ajouter les données à la couche `limite_commune`
                        limite_commune.addData(json_commune);
                        // 5. Ajuster le zoom de la carte aux limites de la géométrie reçue
                        if (limite_commune.getBounds().isValid()) {
                            carte.fitBounds(limite_commune.getBounds());
                            console.log('Zoom effectué sur la commune ID:', id_commune);

                            // L'appel AJAX pour les statistiques de risques
                            var url_stats = 'ajax.php?elemid=information_commune&id_commune=' + id_commune;
                            var xhr_stats = getXhr();
                            xhr_stats.onreadystatechange = function() {
                                if (xhr_stats.readyState == 4 && xhr_stats.status === 200) {
                                    console.log('Réponse brute des stats pour ID ' + id_commune + ':', xhr_stats.responseText);
                                    try {
                                        var stats = JSON.parse(xhr_stats.responseText);
                                        if (stats.error) {
                                            console.error('Erreur AJAX signalée par PHP (stats):', stats.error);
                                            document.getElementById('nombre_inondation').textContent = 'Erreur';
                                            document.getElementById('nombre_erosion').textContent = 'Erreur';
                                            document.getElementById('nombre_eboulement').textContent = 'Erreur';
                                            document.getElementById('nombre_effondrement').textContent = 'Erreur';
                                            document.getElementById('nombre_risque').textContent = 'Erreur';
                                            return;
                                        }
                                        // --- METTRE À JOUR LES ÉLÉMENTS HTML AVEC LES STATISTIQUES ---
                                        document.getElementById('nombre_inondation').textContent = stats.tt_inondation !== undefined ? stats.tt_inondation : 'N/A';
                                        document.getElementById('nombre_erosion').textContent = stats.tt_erosion !== undefined ? stats.tt_erosion : 'N/A';
                                        document.getElementById('nombre_eboulement').textContent = stats.tt_eboulement !== undefined ? stats.tt_eboulement : 'N/A';
                                        document.getElementById('nombre_effondrement').textContent = stats.tt_effondrement !== undefined ? stats.tt_effondrement : 'N/A';
                                        document.getElementById('nombre_risque').textContent = stats.tt_risques !== undefined ? stats.tt_risques : '0';
                                        // --- FIN DE LA MISE À JOUR ---

                                            // --- METTRE À JOUR LE type de GRAPHIQUE ---
                                                         updatePieChart(stats);
                                                        // --- FIN DE L'AJOUT ---

                                        // L'appel AJAX pour les coordonnées des risques et les popups
                                        var url_risks_coords = 'ajax.php?elemid=get_risks_coordinates&id_commune=' + id_commune;
                                        var xhr_risks_coords = getXhr();
                                        xhr_risks_coords.onreadystatechange = function() {
                                            if (xhr_risks_coords.readyState === 4 && xhr_risks_coords.status === 200) {
                                                console.log('Réponse brute (coordonnées risques) pour ID ' + id_commune + ':', xhr_risks_coords.responseText);
                                                try {
                                                    var risks_data = JSON.parse(xhr_risks_coords.responseText);
                                                    if (risks_data.error) {
                                                        console.error('Erreur AJAX signalée par PHP (coordonnées risques):', risks_data.error);
                                                        return;
                                                    }
                                                   
                                               risks_data.forEach(function(risk) {
                                                    // Ne pas afficher les risques qui ne sont pas "traités"
                                                    if (risk.new_statut === 'traite') {
                                                        addRiskMarker(risk, false);
                                                    }
                                                });
                                                    // Assurez-vous que les couches catégorisées sont ajoutées à la carte si elles ne l'étaient pas déjà
                                                    if (!carte.hasLayer(inondationLayer)) inondationLayer.addTo(carte);
                                                    if (!carte.hasLayer(erosionLayer)) erosionLayer.addTo(carte);
                                                    if (!carte.hasLayer(eboulementLayer)) eboulementLayer.addTo(carte);
                                                    if (!carte.hasLayer(effondrementLayer)) effondrementLayer.addTo(carte);

                                                } catch (e) {
                                                    console.error('Erreur de parsing JSON pour les coordonnées des risques:', e);
                                                }
                                            } else if (xhr_risks_coords.readyState === 4) {
                                                console.error('Erreur HTTP lors de la récupération des coordonnées des risques:', xhr_risks_coords.status, xhr_risks_coords.responseText);
                                            }
                                        };
                                        xhr_risks_coords.open("GET", url_risks_coords, true);
                                        xhr_risks_coords.send(null);

                                    } catch (e) {
                                        console.error('Erreur de parsing JSON pour les statistiques:', e);
                                        document.getElementById('nombre_inondation').textContent = 'Erreur';
                                        document.getElementById('nombre_erosion').textContent = 'Erreur';
                                        document.getElementById('nombre_eboulement').textContent = 'Erreur';
                                        document.getElementById('nombre_effondrement').textContent = 'Erreur';
                                        document.getElementById('nombre_risque').textContent = 'Erreur';
                                    }
                                    
                                } else if (xhr_stats.readyState === 4) {
                                    console.error('Erreur HTTP lors de la récupération des stats:', xhr_stats.status, xhr_stats.responseText);
                                }
                            };
                            xhr_stats.open("GET", url_stats, true);
                            xhr_stats.send(null);

                        } else {
                            console.warn('La couche limite_commune n\'a pas de limites valides pour zoomer, même après l\'ajout de données.');
                            alert('Impossible de zoomer sur cette commune : géométrie invalide ou absente.');
                        }
                    } else {
                        console.warn('Aucune fonctionnalité GeoJSON trouvée pour cette commune ou réponse vide.');
                        alert('Aucune donnée géographique trouvée pour cette commune.');
                    }
                } catch (e) {
                    console.error('Erreur de parsing JSON pour le zoom de la commune:', e);
                    alert('Erreur lors de l\'interprétation des données de la commune.');
                }
            } else if (xhr_zoom.readyState === 4) {
                console.error('Erreur HTTP lors du zoom de la commune:', xhr_zoom.status, xhr_zoom.responseText);
            }
        };
        xhr_zoom.open("GET", url_zoom, true);
        xhr_zoom.send(null);
    }

    // --- Chargement initial des couches (Après la définition des couches et la fonction getXhr) ---
    $(document).ready(function() {
        // Charger les données de la couche principale des communes d'Abidjan
        $.getJSON("data/communes_abidjan.json", function(donnee) {
            communes_abidjan.addData(donnee);
            communes_abidjan.addTo(carte); // Ajoute la couche de toutes les communes à la carte

            // Ajuste le zoom initial de la carte pour inclure toutes les communes chargées
            if (communes_abidjan.getBounds().isValid()) {
                carte.fitBounds(communes_abidjan.getBounds());
                console.log('Zoom initial sur toutes les communes effectué.');
            } else {
                console.warn('La couche communes_abidjan n\'a pas de géométrie valide pour le zoom initial.');
                alert('Impossible de zoomer, la géométrie retournée est dégénérée ou trop petite.');
            }
        }).fail(function(jqxhr, textStatus, error) {
            var err = textStatus + ", " + error;
            console.error("Erreur lors du chargement de communes_abidjan.json: " + err);
        });

        // Appel initial pour charger tous les risques sur la carte
        loadAllRisksInitially();
        // Initialiser les graphiques
        initPieChart();
        initBarChart();
        initLineChart();
    // Charger les risques initialement et mettre à jour le diagramme
        loadAllRisksInitially(); 
        // Démarre la vérification régulière des nouveaux risques toutes les 15 secondes
        setInterval(checkForNewRisks, 15000);
    });

    document.addEventListener('DOMContentLoaded', initChart);
</script>
    <!-- jQuery UI 1.11.4 -->
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
      $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 3.3.5 -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <!-- Morris.js charts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <!-- daterangepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js"></script>

</div><!-- /.wrapper -->
  </body>
</html>