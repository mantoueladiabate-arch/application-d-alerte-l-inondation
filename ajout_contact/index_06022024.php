<?php
// connection à la base données
  function connect() {
    $conn = null;

    try{
      $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','postgres'); // On se connecte à la base de donnée
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
                 
      <div>
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
             
            </select>

            <label for="risque"> Risques naturels: </label>
            <select name="riqsue" id="risque">
            <option value=""> Choisez votre commune </option>
             <option value="risque"> Inondation </option> 
             <option value="risque"> Erosion </option>
             <option value="risque"> Eboulement </option>
             <option value="risque"> Effondrement </option>
            
            </select>


            <div >
					<label for="observation">Déscrption </label>
					<textarea name="observation" id="observation" rows="3" cols="30"></textarea>
        
				</div>
				<div >
					<input type="file" class= "form-control-file" id="choisir_des_fichiers">
					<p class="text"> Types de fichiers autorisés: .xls .doc .docx .pdf .odt txt .jpg .png</p>
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
					<input type="submit"  name="submit" value="Envoyer" id="bouton_envoie" class="form-control"  style="width:auto;"/>
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
          // carte.on("click", mapClickListen);



          

          // ecouter l'evenement quartier
         //document.querySelector("form").addEventListener("blur,getCommune")

         // on crée un evenement
        // function mapClickListen(even){
           // on recupere les coordonnées du clic
       // let pos = even.latlng
        // on ajoute un marqueur
       // addMarker(pos)
         //on affiche les coordonnées dans le formulaire
      // document.querySelector("#lat").value = pos.lat
     // document.querySelector("#lon").value = pos.lng
      //  }

        //on ajoute le marqueur
        //function adddMarker(pos){
          //on verifie si un marqueur existe
        // if(marqueur !=undefined){
           // carte.removelayer(marqueur)
        // }
       //   let marqueur = L.marker(pos)
       //   marqueur.addTo(carte)
     //   }




          // ecouter l'evenement quartier
         //document.querySelector("#commune").addEventListener("blur,getCommune")

         // on crée un evenement
        // function mapClickListen(even){
           // on recupere les coordonnées du clic
       // let pos = even.latlng
        // on ajoute un marqueur
       // addMarker(pos)
         //on affiche les coordonnées dans le formulaire
      // document.querySelector("#lat").value = pos.lat
     // document.querySelector("#lon").value = pos.lng
      //  }

        //on ajoute le marqueur
        //function adddMarker(pos){
          //on verifie si un marqueur existe
        // if(marqueur !=undefined){
           // carte.removelayer(marqueur)
        // }
       //   let marqueur = L.marker(pos)
       //   marqueur.addTo(carte)
     //   }




        
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

          //alert(json.features);
 //Cette ligne de code ajoutera le nom des commune à un popup, qui s'ouvrira lorsque l'utilisateur cliquera sur la limite du quartier sur la carte
   //  var nom_commune = JSON.stringify(json.features[0].properties.nom_commune);
   //  limite_commune.bindPopup(nom_commune);

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
  


       
   //$("form").submit(fonction(e){
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
  //})







  

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

  </body>
</html>
