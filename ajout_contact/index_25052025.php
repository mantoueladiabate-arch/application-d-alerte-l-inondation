<?php
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


    //Parcourir la table des résultats de la requête
	while($row = $result->fetch(PDO::FETCH_ASSOC)){
    //affiche le contenu de la table
    $liste_commune.= '<option value="'.$row['id'].'">'.$row['nom_commune'].'</option>';
	}     

// Ajouter les informations si le formulaire est soumis
 if ($_SERVER["REQUEST_METHOD"] == "POST" ) {
  if (isset($_POST['commune'], $_POST['quartier'], $_POST['risques'],
 $_POST['description'], $_POST['longitude'], $_POST['latitude'], $_FILES['file'] )) {
 // récupere les données du formulaire
 $commune = $_POST['commune'];
 $quartier = $_POST['quartier'];
  $risques = $_POST['risques'];
  $description = $_POST['description'];
  $longitude = $_POST['longitude'];
  $latitude = $_POST['latitude'];
 $file = $_FILES['file']['name'];
} //else {
 // echo "Erreur : certaines données du formulaire sont absentes.";
//exit();
//}

 // if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image'])) {
 // $titre = $_POST['titre'];
 // $descriptio = $_POST['descriptio'];
 // $image = $_FILES['image'];

  // Nom de l'image et chemin
 // $imageName = basename($image['name']);
  //$targetDir = "photos/";
  //$targetFile = $targetDir . $imageName;



  // Gestion du telechargement du fichier et chemin
  //Doosier de stockage est photos
  $targetDir = "informations/";
  // Vérifier si le dossier existe et le créer si nécessaire
if (!is_dir($targetDir)) {
  mkdir($targetDir, 0777, true);
}

// Vérifier que $_FILES contient bien un fichier
if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
  die("Erreur : Aucun fichier reçu.");
}

 $file = $_FILES['file']['name']; // Correction
 // Vérification des erreurs de téléchargement
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die("Erreur lors du téléchargement du fichier : " . $_FILES['file']['error']);
}

// Définition du chemin complet du fichier
 $targetFile = $targetDir . basename($file);

  // deplacer le fichier telecharger dans le dossier
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)){ 
      echo "Fichier telecharger avec succés : $file<br>";
   } else {
     echo "Erreur lors du téléchargement du fichier.";
  }


        // Ajouter les données dans la table de la base de données
        $stmt = $conn->prepare("
        INSERT INTO informations (commune, quartier, risques, description, fichier, localisation) 
        VALUES (:commune, :quartier, :risques, :description, :fichier, ST_SetSRID(ST_Point(:longitude, :latitude), 4326))
        ");
        $stmt->bindParam(':commune', $commune);
        $stmt->bindParam(':quartier', $quartier);
       $stmt->bindParam(':risques', $risques);
       $stmt->bindParam(':description', $description);
       $stmt->bindParam(':fichier', $file);
       $stmt->bindParam(':longitude', $longitude);
       $stmt->bindParam(':latitude', $latitude);

      if ($stmt->execute()) {

      echo "Données insérées avec succès.";
  } else {
        echo "Erreur lors de l'insertion des données.";
}

}
//}




  // Vérification si c'est une image
  //if (getimagesize($image['tmp_name'])) {
      //if (move_uploaded_file($image['tmp_name'], $targetFile)) {
          // Ajouter dans la base de données
        //  $stmt = $conn->prepare("INSERT INTO photos (titre, descriptio, chemin_image) VALUES (?, ?, ?)");
        //  $stmt->execute([$titre, $descriptio, $imageName]);
        //  echo "<div class='alert alert-success'>Photo ajoutée avec succès! <a href='../phototheque/index.php'>Voir la phototheque</a></div>";
      //} else {
          //echo "<div class='alert alert-danger'>Désolé, il y a eu une erreur lors du téléchargement de l'image.</div>";
      //}
  //} else {
    //  echo "<div class='alert alert-danger'>Ce n'est pas une image ou une vidéo valide!</div>";
  //}
//}


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

   
      /*.upload-section {
            margin-top: 50px;
        }*/

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

      <form action="#" method="post">

                        

               <label for="commune"> Commune: </label>
            <select id="commune" onchange="zoomCommune(this.value);">
              <option value=""> Choisez votre commune </option>
              <?php echo $liste_commune; ?>
            </select>
       
               
     
         <div >
				<label for="quartier">Quartier:</label>
				<input type="text" name="quartier" id="quartier" placeholder="quartier"/>
			</div> 

     <!--  à supprimer <div>
				<label for="sexe"> Homme </label>
				<input type="radio" name="sexe" id="homme"/>
				<label for="sexe"> Femme </label>
				<input type="radio" name="sexe" id="femme"/>
			</div>

            <label for="age"> Tranche d'age: </label>
            <select name="age" id="age">
              <option value=""> Choisez votre tranche d'âge </option>
              <option value="age">14 à 21</option> 
              <option value="age">21 à 35</option>
              <option value="age">35 à 49</option>
              <option value="age">49 à 56</option>
              <option value="age">56 et plus</option>
             
            </select> à supprimer-->

            <label for="risque"> Risques naturels: </label>
            <select name="riqsue" id="risque">
            <option value=""> Choisez votre commune </option>
             <option value="risque"> Inondation </option> 
             <option value="risque"> Erosion </option>
             <option value="risque"> Eboulement </option>
             <option value="risque"> Effondrement </option>
            
            </select>


            <div >
					<label for="descriptio">Déscrption photo ou vidéo</label>
					<textarea name="descriptio" id="descriptio" rows="3" cols="30"></textarea>
        
				</div>
				
          <div class="mb-3">
                <label for="file" class="form-label">Importer une photo ou vidéo:</label>
                <input type="file" class="form-control" id="file" name="file" required>
            </div>
				<div>
        <div >
				<label for="lat">Latitude:</label>
				<input type="text"  name="lat" id="lat"/>
			</div>
      <div >
				<label for="lon">Longitude:</label>
				<input type="text"  name="lon" id="lon" />
			</div>
					<!--Creer un bouton pour envoyer les fichiers-->
				<!--	<input type="submit"  name="submit" value="Envoyer" id="bouton_envoie" class="form-control"  style="width:auto;"/>-->
          <button type="submit" class="btn btn-primary">Envoyer</button>
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


        //on ajoute le marqueur
        function adddMarker(pos){
          //on verifie si un marqueur existe
        if(marqueur !=undefined){
           carte.removelayer(marqueur)
        }
        let marqueur = L.marker(pos)
         marqueur.addTo(carte)
       }

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


			  //Ajouter fond  de carte gratuit  à notre carte
			    var osm = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(carte)
         
          //Déclarer le marqueur globalement
         var marqueur;
           //ajouter un evenement 'click'pour placer un marqueur
          // carte.on("click", mapClickListen);
           //Vérifier si la carte est bien initialiser
          if (carte){
          carte.on('click', (e) => {
            // console.log("Ok", e.latlng)
            //variable pour stocker le marqueur actif
            //let marqueur = L.marker(e.latlng)
// suprimer le marqeur précédent s'il existe
if (marqueur) {
  carte.removeLayer(marqueur);
}
            // on ajoute un nouveau marqueur
            marqueur= L.marker(e.latlng).addTo(carte);
            //on affiche les coordonnées dans le formulaire
            document.querySelector("#lat").value = e.latlng.lat
            document.querySelector("#lon").value = e.latlng.lng
          });

        }
          else{
          console.eror ("Erreur : La carte n'est pas correctement initialisé !")

        }

          

          
           //ajouter un evenement 'click'pour placer un marqueur
          // carte.on("click", mapClickListen);
           //Vérifier si la carte est bien initialiser
          //carte.on('click', (e) => {
            // console.log("Ok", e.latlng)
            //variable pour stocker le marqueur actif
            //let marqueur = L.marker(e.latlng)
            //carte.removeLayer(marqueur);
            // on ajoute un nouveau marqueur
           // marqueur.addTo(carte);
            //on affiche les coordonnées dans le formulaire
           // document.querySelector("#lat").value = e.latlng.lat
           // document.querySelector("#lon").value = e.latlng.lng
          //});

        
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
   if(xhr.readyState == 4) {
     retour = xhr.responseText; 

 //convertir JSON en un objet JavaScript
 var json = JSON.parse(retour);
         //ajouter les données à la couche
   limite_commune.addData(json); 
         //Ajouter la couche à la carte 
   limite_commune.addTo(carte); 

          //à supprimer alert(json.features);
 //Cette ligne de code ajoutera le nom des commune à un popup, qui s'ouvrira lorsque l'utilisateur cliquera sur la limite du quartier sur la carte
   //  var nom_commune = JSON.stringify(json.features[0].properties.nom_commune);
   //  limite_commune.bindPopup(nom_commune) à supprimer;

     carte.fitBounds(limite_commune.getBounds()); 
       }
     }
//on declenche l'evenement
     xhr.open("GET",url,true);
     //on declenchela demande de la ressource
     xhr.send(null);
   }
 //on veut recuperer le fichier
   function getXhr(){5283
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
  


       
   //a supprimer        $("form").submit(fonction(e){
   // e.preventDefault();
   // $.post(
    //  'ajout.php',//lien du fichier php
   //   {
      //  commune:$("commune").val(),
    //    quartier:$("quartier").val(),
    //    sexe:$("sexe").val(),
     //   age:$("age").val(),
      //  risque:$("risque").val(),
     //   observation:$("observation").val(),
     //   file:$("file").val(),
      //  lat:$("lat").val(),
     //   lon:$("lon").val(),
     // }

   // )
  //} a supprimer)







  

  $.getJSON("../data/communes_abidjan.json",function(donnee){ 
				//ajouter les données à la couche
				 communes_abidjan.addData(donnee); 
         //Ajouter la couche à la carte 
          communes_abidjan.addTo(carte);

        });

        // $(document).ready(function() {
		
	carte.fitBounds(communes_abidjan.getBounds());
		
	  //  });

		  </script>


      

           <!-- jQuery 2.1.4 -->
           <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
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
    <script src="plugins/morris/morris.min.js"></script>
    <!-- Sparkline -->
    <script src="plugins/sparkline/jquery.sparkline.min.js"></script>
    <!-- jvectormap -->
    <script src="plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
    <!-- jQuery Knob Chart -->
    <script src="plugins/knob/jquery.knob.js"></script>
    <!-- daterangepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>
    <!-- datepicker -->
    <script src="plugins/datepicker/bootstrap-datepicker.js"></script>
    <!-- Bootstrap WYSIHTML5 -->
    <script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
    <!-- Slimscroll -->
    <script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="plugins/fastclick/fastclick.min.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/app.min.js"></script>
    
        
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>