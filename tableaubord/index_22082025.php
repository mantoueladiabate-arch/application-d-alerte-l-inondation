<?php
// connection à la base données
  function connect() {
    $conn = null;

    try{
      $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','0151516084');
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
    // Si la fonction connect() n'a pas pu se connecter et n'a pas fait exit(), on le fait ici.
    // Cependant, avec l'exit() ajouté dans connect(), ce bloc ne devrait normalement pas être atteint.
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
$sql_risques = "SELECT count(*) AS total_risque FROM informations WHERE new_statut = 'traite' AND risques IN ('Inondation', 'Erosion', 'Eboulement', 'Effondrement')";
//$sql_risques = "SELECT count(*) AS total_risque FROM informations WHERE risques IN ('Inondation', 'Erosion', 'Eboulement', 'Effondrement')";
//Executer la requete
$result_risques = $conn->query($sql_risques);
//Parcourir la table des résultats de la requête
$row_risques = $result_risques->fetch(PDO::FETCH_ASSOC);
$total_risque = $row_risques['total_risque'];

//Définition de la requête SQL
$sql_Inondation = "SELECT count(*) AS total_inondation FROM informations WHERE new_statut = 'traite' AND risques = 'Inondation'";
//  $sql_Inondation = "SELECT count(*) AS total_inondation FROM informations WHERE risques = 'Inondation'";
  //Executer la requete
  $result_Inondation = $conn->query($sql_Inondation);
  //Parcourir la table des résultats de la requête
  $row_Inondation = $result_Inondation->fetch(PDO::FETCH_ASSOC);
  $total_inondation = $row_Inondation['total_inondation'];

//Définition de la requête SQL
$sql_Erosion = "SELECT count(*) AS total_erosion FROM informations WHERE new_statut = 'traite' AND risques = 'Erosion'";
//$sql_Erosion = "SELECT count(*) AS total_erosion FROM informations WHERE risques = 'Erosion'";
//Executer la requete
$result_Erosion = $conn->query($sql_Erosion);
//Parcourir la table des résultats de la requête
$row_Erosion = $result_Erosion->fetch(PDO::FETCH_ASSOC);
$total_erosion = $row_Erosion['total_erosion'];

//Définition de la requête SQL
$sql_Eboulement = "SELECT count(*) AS total_eboulement FROM informations WHERE new_statut = 'traite' AND risques = 'Eboulement'";
//$sql_Eboulement = "SELECT count(*) AS total_eboulement FROM informations WHERE risques = 'Eboulement'";
//Executer la requete
$result_Eboulement = $conn->query($sql_Eboulement);
//Parcourir la table des résultats de la requête
$row_Eboulement = $result_Eboulement->fetch(PDO::FETCH_ASSOC);
$total_eboulement = $row_Eboulement['total_eboulement'];

//Définition de la requête SQL
$sql_Effondrement = "SELECT count(*) AS total_effondrement FROM informations WHERE new_statut = 'traite' AND risques = 'Effondrement'";
//$sql_Effondrement = "SELECT count(*) AS total_effondrement FROM informations WHERE risques = 'Effondrement'";
//Executer la requete
$result_Effondrement = $conn->query($sql_Effondrement);
//Parcourir la table des résultats de la requête
$row_Effondrement = $result_Effondrement->fetch(PDO::FETCH_ASSOC);
$total_effondrement = $row_Effondrement['total_effondrement'];
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
    
    <style>
*contenant du navigateur du titre*/
#contenu {
      text-align: center;
    }
      .sidebar-form {
        padding: 10px 0;
      }
      /*contenant du navigateur du titre des type de risque*/
      .box {
    all: unset;
}
        .box {
  display: flex;
      align-items: center;
      justify-content: center;
}
.box div {
  width: 250px; /*largeur*/
  height: 100px; /*largeur*/
}
.small-box p {
  font-size: 16px;
}
      /*la carte*/
      #mapid {
        border : 0px solid green;
        width: 100%; /*largueur de l'image*/
        height:580px; /*hauteur de l'image*/
        margin: auto;
        float:right;
        }
/*la legende*/
        .ocsControl leaflet-control,{
        background:
        padding: 5px;
        border-radius: 5px;
      }
    /*logo*/
        img {
        width: 100%; /*largueur de l'image*/
        height:100px; /*hauteur de l'image*/
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
<body >
 <div class="hold-transition skin-blue sidebar-mini">

<a href="../tableaubord/index.php" class="logo">
      <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
          <!-- Sidebar user panel -->         
         <!-- search form -->
          <form action="#"method="get" class="sidebar-form">
            <div class="input-group">
             <!--input type="text"name="q" class="form-control" placeholder="Search...">
               <span class="input-group-btn">
                 <button type="submit"name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
               </span-->
            </div>
          </form>       
          <!-- /.search form -->
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">       
            <li class="active treeview">
              <a href="#"><li class="active"><a href="index.php"><i class="fa fa-circle-o"></i> Accueil </a></li></a>
            </li>           
            <li class="treeview">
              <a href="tableaubord/index.php"> <i class="fa fa-table"></i> <span>Tableau de bord</span></i></a>
            </li>      
           
           <li class="treeview">
              <a href="#"><i class="fa fa-circle-o"></i><span> Commune </span><i class="fa fa-angle-left pull-right"></i></a>
              
                <ul class="treeview-menu">
                <?php echo $liste_commune; ?>
                </ul> 
            </li>
              <li class="treeview">
              <a href="statistique/index.php"> <i class="fa fa-table"></i> <span>Statistique</span></i></a>
            </li>  
            <li class="treeview">
              <a href="contact/index.php"><i class="fa fa-phone"></i><span>Emmetre une alerte</span></a>
            </li>
            <li class="treeview">
              <a href="analyste/index.php"><i class="fa fa-phone"></i><span>Analystes</span></a>
            </li>
            <li class="treeview">
              <a href="historiques/index.php"><i class="fa fa-history"></i><span>Historiques</span></a>
            </li>
             <li class="treeview">
              <a href="documentation/index.php"> <i class="fa fa-book"></i> <span>Galerie</span></i></a>
            </li>
              <li class="treeview">
              <a href="ajout_contact/index.php"><i class="fa fa-phone"></i><span>Enregistrer un contact</span></a>
            </li>
             <li class="treeview">
              <a href="utilisateurs/index.php"><i class="fa fa-user"></i><span>Gestion des utilisateurs</span></a>
            </li>
          </ul>
    </section>
        <!-- /.sidebar -->
  </aside>
 </div>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
      <!-- Main content -->
      <section class="content">
        <!-- Small boxes (Stat box) -->
        <div class="box">
          <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
              <div class="inner">
        <h3 id="nombre_risque"><?php echo $total_risque; ?></h3>
                <p>Nombre de zones impactées</p>
              </div>
               <!--<div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>-->
            </div>
          </div><!-- ./col -->
          <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-blue">
              <div class="inner">
               <h3 id="nombre_inondation"><?php echo $total_inondation; ?></h3>
                <p>Nombre d'inondation</p>
              </div>
             <!-- ./ <div class="icon">
                <i class="ion ion-person"></i>
              </div>-->
            </div>
          </div><!-- ./col -->
          <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
              <div class="inner">
                <h3 id="nombre_erosion"><?php echo $total_erosion; ?><sup style="font-size: 20px"></sup></h3>
                <p>Nombre d'érosion</p>
              </div>
              <!-- ./<div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>-->
            </div>
          </div><!-- ./col -->
          <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
              <div class="inner">
                <h3 id="nombre_eboulement"><?php echo $total_eboulement; ?><sup style="font-size: 20px"></sup></h3>
                <p>Nombre d'éboulement</p>
              </div>
           <!-- ./ <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div> -->
            </div>
          </div><!-- ./col -->

          <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
              <div class="inner" >
                <h3 id="nombre_effondrement"><?php echo $total_effondrement; ?><sup style="font-size: 20px"></sup></h3>
                <p>Nombre d'éffondrement</p>
              </div>
            
            </div>
          </div><!-- ./col -->
        </div><!-- /.row -->
    </section>

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
 </div> <div class="modal fade" id="riskDetailsModal" tabindex="-1" role="dialog" aria-labelledby="riskDetailsModalLabel" aria-hidden="true">
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
              <textarea class="form-control" id="recommandation" name="recommandation" rows="4" placeholder="Saisissez vos recommandations ici..."></textarea>
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
    var riskMarkers = {};
    // Pour garder une trace des risques déjà notifiés et éviter de les faire clignoter à nouveau
    let notifiedRiskIds = new Set();
    // caracteristique de la couche de limite de commune
    var limite_commune = L.geoJson(null, {
        style: function(feature) {
            return {
                color: 'blue',
                weight: 2,
                fill: true,
                fillColor: 'lightblue',
                fillOpacity: 0.6
            }
        }
    });

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

    // --- Variables et Fonctions pour le diagramme ---
    let risksPieChart;

    // Fonction pour initialiser le diagramme
    function initPieChart() {
        const ctx = document.getElementById('risksPieChart').getContext('2d');
        risksPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Inondation', 'Erosion', 'Eboulement', 'Effondrement'],
                datasets: [{
                    label: 'Répartition des risques',
                    data: [0, 0, 0, 0], // Initialisé à zéro
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)', // Bleu pour Inondation
                        'rgba(255, 206, 86, 0.8)', // Jaune pour Erosion
                        'rgba(75, 192, 192, 0.8)', // Cyan pour Eboulement
                        'rgba(255, 99, 132, 0.8)' // Rouge pour Effondrement
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

    // Fonction pour mettre à jour le diagramme avec de nouvelles données
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

    // --- Fonctions de la carte (existantes) ---
    function getMarkerColor(new_statut, typeRisque) {
        // ... (votre code existant)
        if (new_statut === 'non_traite') {
            return "red";
        } else if (new_statut === 'traite') {
            switch(typeRisque) {
                case 'Inondation': return "darkblue";
                case 'Erosion': return "darkorange";
                case 'Eboulement': return "darkcyan";
                case 'Effondrement': return "darkgreen";
                default: return "grey";
            }
        }
        return "red";
    }

    function addRiskMarker(risk, isNew = false) {
        let lat = risk.latitude;
        let lon = risk.longitude;
        let riskId = risk.id;
        let riskType = risk.risques;
        let riskStatut = risk.new_statut;
        if (lat && lon && riskId) {
            if (riskMarkers[riskId]) {
                allRisksLayer.removeLayer(riskMarkers[riskId]);
                inondationLayer.removeLayer(riskMarkers[riskId]);
                erosionLayer.removeLayer(riskMarkers[riskId]);
                eboulementLayer.removeLayer(riskMarkers[riskId]);
                effondrementLayer.removeLayer(riskMarkers[riskId]);
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
                id: riskId,
                typeRisque: riskType
            });
            if (riskStatut === 'non_traite') {
                marker.on('add', function() {
                    if (this.getElement()) {
                        this.getElement().classList.add('blinking-red');
                    }
                });
            }
            riskMarkers[riskId] = marker;
            marker.on('click', function() {
                showRiskDetailsModal(riskId);
                if (this.getElement() && this.getElement().classList.contains('blinking-red')) {
                    this.getElement().classList.remove('blinking-red');
                }
            });
            allRisksLayer.addLayer(marker);
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
            }
        }
    }
    
    function showRiskDetailsModal(riskId) {
        // ... (votre code existant)
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
                            <p><strong>Recommandation actuelle :</strong> ${risk.recommandation || 'Aucune.'}</p>
                            <p><strong>Statut actuel :</strong> ${risk.new_statut || 'Non défini'}</p>
                        `;
                        if (risk.fichier) {
                            const mediaBasePath = 'contact/informations/';
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
                        $('#riskInfoContent').html(content);
                        $('#modalRiskId').val(riskId);
                        $('#recommandationText').val(risk.recommandation || '');
                        $('#riskStatut').val(risk.new_statut || '');
                        $('#riskDetailsModal').modal('show');
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
    
    $(document).ready(function() {
        $('#recommendationForm').on('submit', function(e) {
            e.preventDefault();
            var formData = {
                id: $('#modalRiskId').val(),
                recommandation: $('#recommandationText').val(),
                new_statut: $('#riskStatut').val()
            };
            var xhr = getXhr();
            xhr.open('POST', 'ajax.php?elemid=update_risk_status', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            $('#recommendationMessage').html('<div class="alert alert-success">Recommandation enregistrée et statut mis à jour !</div>');
                            var newStatut = formData.new_statut;
                            var riskId = formData.id;
                            var marker = riskMarkers[riskId];
                            if (marker) {
                                var riskType = marker.options.typeRisque;
                                marker.setStyle({ fillColor: getMarkerColor(newStatut, riskType) });
                                if (marker.getElement() && marker.getElement().classList.contains('blinking-red')) {
                                    marker.getElement().classList.remove('blinking-red');
                                    if (newStatut === 'traite') {
                                        notifiedRiskIds.delete(riskId);
                                    }
                                } else if (newStatut === 'non_traite') {
                                    if (marker.getElement() && !marker.getElement().classList.contains('blinking-red')) {
                                        marker.getElement().classList.add('blinking-red');
                                    }
                                }
                            }
                            setTimeout(() => {
                                $('#riskDetailsModal').modal('hide');
                                $('#recommendationMessage').empty();
                            }, 1500);
                        } else {
                            $('#recommendationMessage').html('<div class="alert alert-danger">Erreur : ' + (response.error || 'Échec de l\'enregistrement. ') + '</div>');
                        }
                    } catch (e) {
                        console.error('Erreur de parsing JSON après soumission:', e);
                        $('#recommendationMessage').html('<div class="alert alert-danger">Erreur de communication avec le serveur.</div>');
                    }
                } else if (xhr.readyState === 4) {
                    console.error('Erreur HTTP lors de la soumission de la recommandation:', xhr.status, xhr.responseText);
                    $('#recommendationMessage').html('<div class="alert alert-danger">Erreur serveur lors de l\'enregistrement.</div>');
                }
            };
            var params = Object.keys(formData).map(function(key) {
                return encodeURIComponent(key) + '=' + encodeURIComponent(formData[key]);
            }).join('&');
            xhr.send(params);
        });
    });

    // --- Fonctions de la carte (existantes) ---
    function loadAllRisksInitially() {
        var url = 'ajax.php?elemid=get_risks_coordinates&id_commune=all';
        var xhr = getXhr();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        console.error('Erreur au chargement initial des risques:', response.error);
                        return;
                    }
                    allRisksLayer.clearLayers();
                    inondationLayer.clearLayers();
                    erosionLayer.clearLayers();
                    eboulementLayer.clearLayers();
                    effondrementLayer.clearLayers();

                    for (const id in riskMarkers) {
                        if (riskMarkers.hasOwnProperty(id)) {
                            delete riskMarkers[id];
                        }
                    }
                    notifiedRiskIds.clear();

                    let stats = {
                        tt_inondation: 0,
                        tt_erosion: 0,
                        tt_eboulement: 0,
                        tt_effondrement: 0,
                        tt_risques: response.length
                    };

                    response.forEach(function(risk) {
                        addRiskMarker(risk, false);
                        switch (risk.risques) {
                            case 'Inondation': stats.tt_inondation++; break;
                            case 'Erosion': stats.tt_erosion++; break;
                            case 'Eboulement': stats.tt_eboulement++; break;
                            case 'Effondrement': stats.tt_effondrement++; break;
                        }
                    });

                    document.getElementById('nombre_inondation').textContent = stats.tt_inondation;
                    document.getElementById('nombre_erosion').textContent = stats.tt_erosion;
                    document.getElementById('nombre_eboulement').textContent = stats.tt_eboulement;
                    document.getElementById('nombre_effondrement').textContent = stats.tt_effondrement;
                    document.getElementById('nombre_risque').textContent = stats.tt_risques;

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

    function checkForNewRisks() {
        // ... (votre code existant, pas de modification nécessaire ici)
    }

    function zoomCommune(id_commune) {
        // ... (votre code existant)
        limite_commune.clearLayers();
        allRisksLayer.clearLayers();
        inondationLayer.clearLayers();
        erosionLayer.clearLayers();
        eboulementLayer.clearLayers();
        effondrementLayer.clearLayers();

        riskMarkers = {};
        notifiedRiskIds.clear();

        document.getElementById('nombre_risque').textContent = '0';
        document.getElementById('nombre_inondation').textContent = '0';
        document.getElementById('nombre_erosion').textContent = '0';
        document.getElementById('nombre_eboulement').textContent = '0';
        document.getElementById('nombre_effondrement').textContent = '0';
        
        var url_zoom = 'ajax.php?elemid=zoom_commune&id_commune=' + id_commune;
        var url_stats = 'ajax.php?elemid=information_commune&id_commune=' + id_commune;
        var url_risks_coords = 'ajax.php?elemid=get_risks_coordinates&id_commune=' + id_commune;

        var xhr_zoom = getXhr();
        xhr_zoom.onreadystatechange = function() {
            if (xhr_zoom.readyState == 4 && xhr_zoom.status === 200) {
                try {
                    var json_commune = JSON.parse(xhr_zoom.responseText);
                    if (json_commune.error) {
                         console.error('Erreur AJAX signalée par PHP (zoom):', json_commune.error);
                         alert('Erreur lors du zoom: ' + json_commune.error);
                         return;
                     }
                    if (json_commune && json_commune.features && json_commune.features.length > 0) {
                         var firstFeature = json_commune.features[0];
                         if (!firstFeature.geometry || firstFeature.geometry.coordinates.length === 0) {
                             console.warn('La géométrie de la première fonctionnalité est vide ou manquante pour ID ' + id_commune);
                             alert('La géométrie de la commune est vide ou invalide.');
                             return;
                         }

                        limite_commune.addData(json_commune);
                        if (limite_commune.getBounds().isValid()) {
                            carte.fitBounds(limite_commune.getBounds());
                            console.log('Zoom effectué sur la commune ID:', id_commune);

                            // L'appel AJAX pour les statistiques de risques
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
                                        document.getElementById('nombre_inondation').textContent = stats.tt_inondation !== undefined ? stats.tt_inondation : 'N/A';
                                        document.getElementById('nombre_erosion').textContent = stats.tt_erosion !== undefined ? stats.tt_erosion : 'N/A';
                                        document.getElementById('nombre_eboulement').textContent = stats.tt_eboulement !== undefined ? stats.tt_eboulement : 'N/A';
                                        document.getElementById('nombre_effondrement').textContent = stats.tt_effondrement !== undefined ? stats.tt_effondrement : 'N/A';
                                        document.getElementById('nombre_risque').textContent = stats.tt_risques !== undefined ? stats.tt_risques : '0';

                                        // Mise à jour du diagramme
                                        updatePieChart(stats);

                                        // L'appel AJAX pour les coordonnées des risques
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
                                                        addRiskMarker(risk, false);
                                                    });
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


    // --- Chargement initial ---
    $(document).ready(function() {
        // Charger les données de la couche principale des communes d'Abidjan
        $.getJSON("data/communes_abidjan.json", function(donnee) {
            communes_abidjan.addData(donnee);
            communes_abidjan.addTo(carte);
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

        // Initialiser le diagramme circulaire
        initPieChart();
        
        // Charger tous les risques au démarrage de la carte et mettre à jour le graphique
        loadAllRisksInitially();
        
        // Démarre la vérification régulière des nouveaux risques toutes les 15 secondes
        setInterval(checkForNewRisks, 15000);
    });

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

 
  </body>
</html>