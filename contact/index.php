<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// connection à la base données
  function connect() {
    $conn = null;

    try{
      $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','postgres'); // On se connecte à la base de donnée
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } 
    // S'il existe un problème de connection, on obtient le message d'erreur
    catch(PDOException $ex) {
      echo 'Connection error : ' . $ex->getMessage(); 
    }
    return $conn;
  }
$conn=connect();
   $liste_commune="";
//Définition de la requête SQL
   $sql = 'SELECT id, "commune" as nom_commune, "id" FROM public.communes_abidjan';

//Executer la requete
   $result = $conn->query($sql);

   
// Pré-remplir les variables pour le formulaire (mode création ou édition)
$initial_latitude = '';
$initial_longitude = '';
$initial_date = ''; // Nouvelle variable pour la date

// Vérifier si un ID d'information est passé en GET pour l'édition
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_to_load = $_GET['id'];
    try {
        $stmt_load = $conn->prepare('SELECT commune, quartier, risques, description, longitude, latitude FROM public.informations WHERE id = :id_info');
        $stmt_load->bindParam(':id_info', $id_to_load, PDO::PARAM_INT);
        $stmt_load->execute();
        $risk_data_to_load = $stmt_load->fetch(PDO::FETCH_ASSOC);

        if ($risk_data_to_load) {
            $initial_latitude = htmlspecialchars($risk_data_to_load['latitude']);
            $initial_longitude = htmlspecialchars($risk_data_to_load['longitude']);
            $initial_date = !empty($risk_data_to_load['date
            ']) ? date('Y-m-d', strtotime($risk_data_to_load['date'])) : '';
        }
        // Ajoutez ces lignes juste ici:
echo "";
echo "";
    } catch (PDOException $e) {
        error_log("Erreur de chargement du risque: " . $e->getMessage());
    }
}

// Parcourir la table des résultats de la requête pour les communes
// Cette boucle doit être exécutée après la récupération de $initial_commune_id
// Et avant l'affichage du HTML
$result->execute(); // Réinitialiser le pointeur pour parcourir à nouveau


    //Parcourir la table des résultats de la requête
while($row = $result->fetch(PDO::FETCH_ASSOC)){
    //affiche le contenu de la table
   $liste_commune.= '<option value="'.$row['id'].'">'.$row['nom_commune'].'</option>';
	}     

// Ajouter les informations si le formulaire est soumis
 if ($_SERVER["REQUEST_METHOD"] == "POST" ) {

 // récupere les données du formulaire
 $commune = $_POST['commune'];
 $quartier = $_POST['quartier'];
  $risques = $_POST['risque'];
  $description = $_POST['description'];
  $date = $_POST['date']; // Récupérer la date du formulaire
  $longitude = $_POST['longitude'];
  $latitude = $_POST['latitude'];
 $file = $_FILES['file']['name'];

  
  //Doosier de stockage des photos et vidéo
  $targetDir = "informations/";
  // Vérifier si le dossier existe et le créer si nécessaire
if (!is_dir($targetDir)) {
  mkdir($targetDir, 0777, true);
}

// Vérifier que $_FILES contient bien un fichier
if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
  die("Erreur : Aucun fichier reçu.");
}
    $targetFile = $targetDir . basename($file);

 // Vérification des erreurs de téléchargement
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die("Erreur lors du téléchargement du fichier : " . $_FILES['file']['error']);
}


  // deplacer le fichier telecharger dans le dossier
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)){ 
    
   } else {
    echo "Erreur lors du téléchargement du fichier.";
  }
  
        // Ajouter les données dans la table de la base de données
        $stmt = $conn->prepare("
        INSERT INTO informations (commune, quartier, risques, description, date, fichier, longitude, latitude) 
        VALUES (:commune, :quartier, :risques, :description, :date, :fichier, :longitude, :latitude)
        ");
        $stmt->bindParam(':commune', $commune);
        $stmt->bindParam(':quartier', $quartier);
       $stmt->bindParam(':risques', $risques);
       $stmt->bindParam(':description', $description);
        $stmt->bindParam(':date', $date); // Binde la nouvelle variable
       $stmt->bindParam(':fichier', $file);
       $stmt->bindParam(':longitude', $longitude);
       $stmt->bindParam(':latitude', $latitude);
      if ($stmt->execute()) {
echo "";
    
 } else {
        echo "Erreur lors de l'insertion des données.";
}

}

?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Contact</title>
  <!--liens de leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
		<script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-groupedlayercontrol/0.6.1/leaflet.groupedlayercontrol.min.js"></script>
    
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">
    <!-- <link rel="stylesheet" href="style.css"> -->
    


    <style>
      /*enlever le style des elements par defaut*/
      *{
        padding: 0;
        margin: 0;
        box-sizing: border-box;
      }

    
      .navbar{
         /*hauteur*/
			   height:5vh;
        /*largueur de l'image*/
        width: 100%; 
        /*centrer le contenu*/
        align-items: center;
        justify-content: center;
          
		    }

      .txt{
        /*si jamais le texte depasse pas de retour à la ligne*/
        white-space: nowrap;
        /*police de l'ecriture*/
        font-size: 30px;
        /*animation du texte*/
        animation: scroll 10s linear infinite;      
      }

        /*animation du texte*/
      @keyframes scroll {
        /* 0% */ from {
        /*decalage du texte tout à gauche*/
        transform: translateX(-100%);
        margin-left:100%
       }
        100% {
         transform: translateX(0%); 
        }
      }

      /*police et couleur du texte*/
      body {
        /*hauteur*/
			  height:100vh;
        display:flex;
        /*centrer la boite*/
        align-items: center;
        justify-content: center;
         /*mettre l'image en fond*/
        background-image:url('images/eau.jpeg');
         /*adapter l'image à notre ecran*/
        background-size:cover;
          
		  }

      .contener {
			  width:75%;/*largueur 75% du parent*/
        max-width:2000px;	/*largueur maximal de boite*/
        box-shadow: 0 0 15px /*(0,0,0,3) /*opatiter de la boite*/ ;
        display: flex;
        align-items: center; /*centrage horizontal entre le texte et la carte*/
        font-size: 18px; /*taille*/  
       /* background-color: blue; */ 

		  }
   
     /*largeur et centrer du texte*/
      form {
          padding:30px 60px;     
		  }

    /*la carte*/
     #mapid {
      border : 0px solid green;
			width: 100%; /*largueur de l'image*/
			height:550px; /*hauteur de l'image*/
			margin: auto;
			float:right;  
		  } 

   
     

    </style>


  </head>
  <body class="skin-blue fixed" data-spy="scroll" data-target="#scrollspy">
    <div>

      <header class="main-header">
        <!-- Logo -->
        
        <a href="../index.php" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>A</b>LT</span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>Acceuil</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
         <span class="txt">Aider nous à rendre nos donnée plus precis.
         Fournissez-nous des données sur votre commune et quartier</span> 
          
        </nav>
      </header>
    </div>
    

    <div class="contener">
    
<!-- Creer un bouton pour envoyer les fichiers-->

      <form action="index.php" method="post" enctype="multipart/form-data">

                        

               <label for="commune"> Commune: </label>
            <select id="commune" name="commune" onchange="zoomCommune(this.value);">
              <option value=""> Choisez votre commune </option>
              <?php echo $liste_commune; ?>
            </select>
       
               
         <div >
				<label for="quartier">Quartier:</label>
				<input type="text" name="quartier" id="quartier" placeholder="quartier"/>
			</div> 
       
         <!--div >
				<label for="quartier">Quartier:</label>
				<input type="text" name="quartier" id="quartier" placeholder="quartier"/>
			</div--> 

  

            <label for="risque"> Risques naturels: </label>
            <select name="risque" id="risque">
            <option value=""> Choisez un </option>
             <option value="Inondation"> Inondation </option> 
             <option value="Erosion"> Erosion </option>
             <option value="Eboulement"> Eboulement </option>
             <option value="Effondrement"> Effondrement </option>
            
            </select>


 <div>
                <label for="date">Date de l'événement:</label>
                <input type="date" name="date" id="date" value="<?php echo $initial_date; ?>" />
            </div>

            <div >
					<label for="description">Déscrption photo ou vidéo</label>
					<textarea name="description" id="description" rows="3" cols="30"></textarea>
        
				</div>
				
          <div class="mb-3">
                <label for="file" class="form-label">Importer une photo ou vidéo:</label>
                <input type="file" class="form-control" id="file" name="file" required>
            </div>
				<div>
        <div >
				<label for="lat">Latitude:</label>
				<input type="text"  name="latitude" id="lat" readonly value="<?php echo $initial_latitude; ?>"/>
			</div>
      <div >
				<label for="lon">Longitude:</label>
				<input type="text"  name="longitude" id="lon" readonly value="<?php echo $initial_longitude; ?>"/>
			</div>
					<!--Creer un bouton pour envoyer les fichiers-->
          <button type="submit" class="btn btn-primary">Envoyer</button>
          
        <a href="../historiques/index.php"class="btn btn-info">Voir l'historique des incidents</a>
        </div>

       
      </form>
  
      <!--Importer le cadre de la carte-->
		<div id="mapid"></div>

        </div> 
 

   <script>

        // Initialiser la carte
        var carte = L.map('mapid', {
          center: [5.3294815, -3.9919594],
          zoom: 11
          });

  //Déclarer le marqueur globalement
         var marqueur = null;

    // caracteristique de la  couche
    var limite_commune = L.geoJson(null,{
        style: function(feature){
          return {
          color: 'black',
          weight: 2,
          fill: true,
          fillColor: 'rgba(255,255,255,0.5)',
          fillOpacity: 0.5
              }
            }
          }).addTo(carte);
var limite_quartier = L.geoJson(null, {
          style: function(feature) {
              return { color: 'red', 
              weight: 2, 
              fill: true, 
              fillColor: 'rgba(255,0,0,0.5)', 
              fillOpacity: 0.5 }
          }
      }).addTo(carte);

        //Ajouter fond  de carte gratuit  à notre carte
          var osm = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(carte)


          //Vérifier si la carte est bien initialiser
          if (carte){
          carte.on('click', (e) => {

            // Ferme toutes les popups actuellement ouvertes sur la carte avant d'en créer une nouvelle
    carte.closePopup();

// suprimer le marqeur précédent s'il existe
if (marqueur) {
  carte.removeLayer(marqueur);
}

// Arrondir les coordonnées pour une meilleure lisibilité
                const lat = e.latlng.lat.toFixed(6);
                const lon = e.latlng.lng.toFixed(6);


          // on ajoute un nouveau marqueur, et on le rend DRAGGABLE (déplaçable)
                marqueur = L.marker([lat, lon], { draggable: true }).addTo(carte);

                // on affiche les coordonnées dans le formulaire
                document.querySelector("#lat").value = lat;
                document.querySelector("#lon").value = lon;

                // Ajouter une popup au marqueur
                marqueur.bindPopup(`Latitude: ${lat}<br>Longitude: ${lon}`).openPopup();

                // Écouter l'événement 'dragend' (quand l'utilisateur lâche le marqueur après l'avoir glissé)
                marqueur.on('dragend', function(event) {
                    const markerLatLng = event.target.getLatLng();
                    const draggedLat = markerLatLng.lat.toFixed(6);
                    const draggedLon = markerLatLng.lng.toFixed(6);

                    // Mettre à jour les champs du formulaire avec les nouvelles coordonnées du marqueur
                    document.querySelector("#lat").value = draggedLat;
                    document.querySelector("#lon").value = draggedLon;
                    // Mettre à jour le contenu de la popup du marqueur
                    marqueur.getPopup().setContent(`Latitude: ${draggedLat}<br>Longitude: ${draggedLon}`);
                    console.log(`Marqueur déplacé vers : Lat=${draggedLat}, Lon=${draggedLon}`); // <-- Correction du console.log
 
                });

          });

        } else{
          console.error ("Erreur : La carte n'est pas correctement initialisée !")
        }


        
          var GoogleStreets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3']
          });


          var GoogleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
            maxZoom: 20,
            subdomains:['mt0','mt1','mt2','mt3']
          });


                // Grouper les couches
          var fonds = {
          OpenStreetMap : osm,
          GoogleMap : GoogleStreets,
          Satellite : GoogleHybrid
          };

          //créer une couche
          var communes_abidjan = L.geoJson("",{
            style: function(feature){
              return {
                color: 'black',
                weight: 2,
                fill:true,
                fillColor: 'rgba(255,255,255,0.5)',
                fillOpacity: 0.4
              }
            }
          }).addTo(carte);

          var donnees = {
              'Limite Abidjan' : communes_abidjan
            };

            // Création outils à droite en haut
          var layerControl = L.control.layers(fonds,donnees);

            // Ajouter à la carte
          carte.addControl(layerControl);

           //Ajouter l'echelle cartographique
      L.control.scale().addTo(carte);



     //Ajouter les couches
     function zoomCommune(id_commune) {


   limite_commune.clearLayers();
   //liaison avec le fichier ajax
   var url = 'ajax.php?elemid=zoom_commune&id_commune='+id_commune;
   var xhr = getXhr();

  // récupérer la réponse
 xhr.onreadystatechange = function() {
   if(xhr.readyState == 4 && xhr.status == 200) {
     retour = xhr.responseText;

 //convertir JSON en un objet JavaScript
 var json = JSON.parse(retour);
         //ajouter les données à la couche
   limite_commune.addData(json);
         //Ajouter la couche à la carte
   limite_commune.addTo(carte);

       }
     }
//on declenche l'evenement
     xhr.open("GET",url,true);
     //on declenchela demande de la ressource
     xhr.send(null);
   } //on veut recuperer le fichier
   function getXhr(){ // J'ai retiré le '5283' qui était une erreur de frappe ici
     var xhr = null;
       if(window.XMLHttpRequest) {
       xhr = new XMLHttpRequest();

     } else if(window.ActiveXObject) {
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

 $.getJSON("../data/communes_abidjan.json",function(donnee){
        //ajouter les données à la couche
         communes_abidjan.addData(donnee);
         //Ajouter la couche à la carte
          communes_abidjan.addTo(carte);

        });


// NOUVEAU BLOC PRINCIPAL POUR L'INITIALISATION AU CHARGEMENT (Logiciel amélioré)
$(document).ready(function() {

    // Fonction pour initialiser la carte avec les coordonnées fournies (que ce soit de la géolocalisation ou de PHP)
    function initializeMarkerAndMap(lat, lon, zoomLevel = 15) {
        if (!isNaN(lat) && !isNaN(lon)) {
            if (marqueur) {
                carte.removeLayer(marqueur);
            }
            marqueur = L.marker([lat, lon], { draggable: true }).addTo(carte);
            marqueur.bindPopup(`Latitude: ${lat.toFixed(6)}<br>Longitude: ${lon.toFixed(6)}`).openPopup();
            carte.setView([lat, lon], zoomLevel);

            marqueur.on('dragend', function(event) {
                const markerLatLng = event.target.getLatLng();
                const draggedLat = markerLatLng.lat.toFixed(6);
                const draggedLon = markerLatLng.lng.toFixed(6);
                document.querySelector("#lat").value = draggedLat;
                document.querySelector("#lon").value = draggedLon;
                marqueur.getPopup().setContent(`Latitude: ${draggedLat}<br>Longitude: ${draggedLon}`);
                console.log(`Marqueur déplacé vers : Lat=${draggedLat}, Lon=${draggedLon}`);
            });
            console.log(`Marqueur initial placé à Lat: ${lat}, Lon: ${lon}`);
        } else {
            console.warn("Coordonnées non valides passées à initializeMarkerAndMap:", lat, lon);
        }
    }


    // 1. Essayer la géolocalisation en premier
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.getElementById('lat').value = position.coords.latitude.toFixed(6);
                document.getElementById('lon').value = position.coords.longitude.toFixed(6);
                initializeMarkerAndMap(position.coords.latitude, position.coords.longitude, 15);
                console.log("Géolocalisation réussie.");
            },
            (error) => {
                console.warn(`Erreur de géolocalisation (${error.code}): ${error.message}`);
                // Si la géolocalisation échoue, tenter d'utiliser les coordonnées de PHP
                tryInitializeFromPHP();
            },
            {enableHighAccuracy: true, timeout: 5000, maximumAge: 0}
        );
    } else {
        console.warn("La géolocalisation n'est pas supportée par ce navigateur.");
        // Si la géolocalisation n'est pas supportée du tout, tenter d'utiliser les coordonnées de PHP
        tryInitializeFromPHP();
    }

    // Fonction pour tenter l'initialisation depuis PHP
    function tryInitializeFromPHP() {
        const initialLat = document.querySelector("#lat").value;
        const initialLon = document.querySelector("#lon").value;

        if (initialLat && initialLon) {
            console.log("Tentative d'initialisation depuis PHP.");
            initializeMarkerAndMap(parseFloat(initialLat), parseFloat(initialLon), 15);
        } else {
            console.log("Pas de coordonnées initiales de PHP. La carte reste sur le centre par défaut.");
        }
    }

    // Gérer le zoom de commune si une est pré-sélectionnée (indépendamment du marqueur initial)
    const initialCommuneId = document.getElementById('commune').value;
    if (initialCommuneId) {
        zoomCommune(initialCommuneId);
    }
});

  </script>      

     
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
 
    <script>
      $.widget.bridge('uibutton', $.ui.button);
    </script>
 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
   
    
        
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>