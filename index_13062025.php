<?php
// connection à la base données
  function connect() {
    $conn = null;

    try{
      $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','postgres');
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
  
  


   //Définition de la requête SQL
   //$sql = 'SELECT id, "area", "commune", "nom_quar" as nom_quartier, "id" FROM public.quartiers_cocody';
//Executer la requete
  // $result = $conn->query($sql);


    //Parcourir la table des résultats de la requête
	//while($row = $result->fetch(PDO::FETCH_ASSOC)){ //headerCommune(id_commune) 
   // $liste_quartier.= '<li><a href="#" onclick="zoomQuartier(\''.$row['id'].'\');">'.$row['nom_quartier'].'</a></li>';
//	}    




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
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/iCheck/flat/blue.css">
    <!-- Morris chart -->
    <link rel="stylesheet" href="plugins/morris/morris.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="plugins/jvectormap/jquery-jvectormap-1.2.2.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="plugins/datepicker/datepicker3.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    


    <style>
/*contenant du navigateur du titre*/
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

}*/

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


		</style>
	

  </head>



<body >
 <div class="hold-transition skin-blue sidebar-mini">
 
 <!-- <div class="wrapper">

  <header class="main-header">
 
</header> -->

      <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
          <!-- Sidebar user panel -->
          <div class="user-panel">
            
            <img src="data/institutgeographietropicale.jpeg"alt="logo de l'igt"/>
           
          </div>
          
         <!-- search form -->
          <form action="#"method="get" class="sidebar-form">
            <div class="input-group">
             <input type="text"name="q" class="form-control" placeholder="Search...">
               <span class="input-group-btn">
                 <button type="submit"name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
               </span>
            </div>
          </form>
         
         
          <!-- /.search form -->
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
            
            <li class="active treeview">
              <a href="#"><li class="active"><a href="index.php"><i class="fa fa-circle-o"></i> Acceuil </a></li></a>
            </li>
            
            <!-- <li class="treeview">
              <a href="prevision/index.html"><i class="fa fa-pie-chart"></i><span>Prévision</span></a>
            </li>-->

           
        
            <li class="treeview">
              <a href="documentation/index.php"> <i class="fa fa-book"></i> <span>Documentation</span></i></a>
            </li>
           
                  <!-- <li class="treeview">
              <a href="#"> <i class="fa fa-share"></i> <span>Phototheque</span><i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Vidéos <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                  
                    <li>
                      <a href="#"><i class="fa fa-circle-o"></i>Années<i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                          <li><a href="phototheque/index.php"><i class="fa fa-circle-o"></i> 2021</a></li>
                          <li><a href="phototheque/index.php"><i class="fa fa-circle-o"></i> 2020</a></li>
                          <li><a href="phototheque/index.php"><i class="fa fa-circle-o"></i> 2019</a></li>
                        </ul>
                    </li>
                  </ul>
                </li>

                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Photos <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                    <li>
                      <a href="#"><i class="fa fa-circle-o"></i> Années <i class="fa fa-angle-left pull-right"></i></a>
                      <ul class="treeview-menu">
                        <li><a href="phototheque/index.php"><i class="fa fa-circle-o"></i> 2021</a></li>
                        <li><a href="phototheque/index.php"><i class="fa fa-circle-o"></i> 2020</a></li>
                        <li><a href="phototheque/index.php"><i class="fa fa-circle-o"></i> 2019</a></li>
                      </ul>
                    </li>
                  </ul>
                </li>
              </ul>
            </li> -->

              <!--<li class="treeview">
              <a href="#"><i class="fa fa-circle-o"></i><span> Quartiers </span><i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                <?//php echo $liste_quartier; ?>
          
                </ul> 
            </li> -->


           <li class="treeview">
              <a href="#"><i class="fa fa-circle-o"></i><span> Commune </span><i class="fa fa-angle-left pull-right"></i></a>
              
                <ul class="treeview-menu">
                <?php echo $liste_commune; ?>
                </ul> 
            </li> 
        

            <!--li class="treeview">
              <a href="#"><i class="fa fa-circle-o"></i> <span>Zones</span><i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                  <li><a href="#">CSS de Base</a></li>
                  <li><a href="#">PHP de Base</a></li>
                  <li><a href="#">JavaScript de Base</a></li>
                </ul>
            </li-->   

            <li class="treeview">
              <a href="contact/index.php"><i class="fa fa-phone"></i><span>Contact</span></a>
            </li>
            
          </ul>
        
           
    </section>
        <!-- /.sidebar -->
  </aside>
 </div>


  <!--div id='contenu'><h2>PREVISION DES RISQUES D'INONDATION</h2></div>
    <div id="main-nav" class="clear-fix"> 
      <div class="container">
        <nav id="site-navigation" class="main-navigation"role="navigation">
          <div class="wrap-menu-content">
            <div class="menu-menu-fr-container">
            <!--wrap-menu-content-->
            <!--/div>
          <!--header-search-box-->      
          <!--/div>
        </nav>
      <!--container-->
      <!--/div>
    </div>
  <!--main-nav-->
  <!--/div-->


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


    <script>

    // Initialiser la carte//
      var carte = L.map('mapid', { 
      center: [5.3294815, -3.9919594], 
      zoom: 13
       });


    // caracteristique de la couche
    //var limite_quartier = L.geoJson(null,{
			//	style: function(feature){
			//		return {
				//	color: 'black',
				//	weight: 2,
			//		fill: true,
				//	fillColor: 'gray',
				//	fillOpacity: 0.5
			//				}
			//			}
				//	});


// caracteristique de la couche
var limite_commune = L.geoJson(null,{
				style: function(feature){
				return {
					color: 'blue', // Couleur pour la commune zoomée
					weight: 2,
					fill: true,
					fillColor: 'lightblue',
					fillOpacity: 0.6
							}
						}
					});

//}).addTo(carte); // Ajout direct à la carte pour qu'elle soit toujours là, mais vide au début

    //Ajouter fond  de carte gratuit  à notre carte//
      var osm = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(carte);

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

// Couche pour toutes les communes (chargée depuis le fichier JSON statique)
				  var communes_abidjan = L.geoJson(null,{
						style: function(feature){
							return {
								color: 'black',
								weight: 2,
								fill:true,
								fillColor: 'gray',
								fillOpacity: 0.1
							}
						}
					}); 

        //créer une couche 
				  //var commune_cocody = L.geoJson(null,{
						//style: function(feature){
						//	return {
							//	color: 'black',
						//		weight: 2,
						//		fill:true,
						//		fillColor: 'gray',
						//		fillOpacity: 0.1
						//	}
					//	}
				//	});
        //créer une couche 
        var bassin_versant = L.geoJson(null,{
						style: function(feature){
							return {
								color: 'black',
								weight: 2,
								fill:true,
								fillColor: 'gray',
								fillOpacity: 0.1
							}
						}
					});

        //créer une couche 
          var couche_geologique = L.geoJson(null,{
						style: function(feature){
							return {
								color: 'black',
								weight: 2,
								fill:true,
								fillColor: 'gray',
								fillOpacity: 0.1
							}
						}
					});

            //créer une couche 
				  var cours_eau = L.geoJson(null,{
						style: function(feature){
							return {
								color: 'black',
								weight: 2,
								fill:true,
								fillColor: 'gray',
								fillOpacity: 0.1
							}
						}
					});

   //créer un couche 
   var densite_drainage = L.geoJson(null,{
						style: function(feature){
							return {
								color: 'black',
								weight: 2,
								fill:true,
								fillColor: 'gray',
								fillOpacity: 0.1
							}
						}
					});

             //créer un couche 
				  var densite_population = L.geoJson(null,{
						style: function(feature){
							return {
								color: 'black',
								weight: 2,
								fill:true,
								fillColor: 'gray',
								fillOpacity: 0.1
							}
						}
					});

          //créer un couche 
				  var isohyete = L.geoJson(null,{
						style: function(feature){
							return {
								color: 'black',
								weight: 2,
								fill:true,
								fillColor: 'gray',
								fillOpacity: 0.1
							}
						}
					});

          //créer un couche 
				  var ocs = L.geoJson(null,{
						style: function(feature){
							return {
								color: 'black',
								weight: 2,
								fill:true,
								fillColor: 'gray',
								fillOpacity: 0.1
							}
						}
					});
          //créer un couche 
				  var slope_pente = L.geoJson(null,{
						style: function(feature){
							return {
								color: 'black',
								weight: 2,
								fill:true,
								fillColor: 'gray',
								fillOpacity: 0.1
							}
						}
					});
   //créer un couche 
   var type_habitat= L.geoJson(null,{
						style: function(feature){
							return {
								color: 'black',
								weight: 2,
								fill:true,
								fillColor: 'gray',
								fillOpacity: 0.1
							}
						}
					});
  

          var donnees = {
            'Limite abidjan' : communes_abidjan, // C'est ici que votre couche principale sera listée
						//	'Limite cocody' : commune_cocody,
              'Bassin versant' : bassin_versant,
              'Couche geologique' : couche_geologique,
              'Densite drainage' : densite_drainage,
              'Densite population' : densite_population,
              'OCS' : ocs,
              'Pente' : slope_pente,
              'Type habitat' : type_habitat,
							
						};	

      
        
    	// Création outils à droite en haut
			var layerControl = L.control.layers(fonds,donnees);
				
				// Ajouter à la carte
			carte.addControl(layerControl);

        //Ajouter l'echelle cartographique
      L.control.scale().addTo(carte);

      //Ajouter la legende à la carte

      // --- Fonctions AJAX et gestion du zoom ---

// Utilisation de la fonction getXhr() comme précédemment
function getXhr(){
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

// Fonction pour récupérer les informations de la commune (statistiques)
      function headerCommune(id_commune) {
        var url = 'ajax.php?elemid=information_commune&id_commune='+id_commune;
        var xhr = getXhr();
        xhr.onreadystatechange = function() {
        if(xhr.readyState == 4) {
          // IMPORTANT : Vérifiez la réponse du serveur
            console.log('Réponse brute de information_commune :', xhr.responseText);
            try {

var retourJson = JSON.parse(xhr.responseText);
                if (retourJson.error) {
                    alert('Erreur: ' + retourJson.error);
                } else {
                    // Mettez à jour vos compteurs ici avec retourJson.tt_inondation, etc.
                    // Par exemple:
                    // document.getElementById('total_inondation_display').innerText = retourJson.tt_inondation;
                    alert('Infos commune : ' + retourJson.tt_inondation + ' inondations, ' + retourJson.tt_erosion + ' érosions, etc.');
                }
            } catch (e) {
                console.error('Erreur de parsing JSON pour information_commune:', e);
                alert('Erreur de communication avec le serveur pour les informations de la commune.');
            }
        }
    }
    xhr.open("GET", url, true);
    xhr.send(null);
}




//Ajouter les couches
     function zoomCommune(id_commune) { 
   

   limite_commune.clearLayers();
   //liaison avec le fichier ajax
   var url_zoom = 'ajax.php?elemid=zoom_commune&id_commune=' + id_commune;
    var xhr_zoom = getXhr();

    // Optionnel: Réinitialiser les compteurs à 0 ou à un état vide avant de charger les nouveaux
     document.getElementById('nombre_risque').textContent = '0';
    document.getElementById('nombre_inondation').textContent = '0';
    document.getElementById('nombre_erosion').textContent = '0';
    document.getElementById('nombre_eboulement').textContent = '0';
    document.getElementById('nombre_effondrement').textContent = '0';

  // récupérer la réponse 
xhr_zoom.onreadystatechange = function() {
    if(xhr_zoom.readyState == 4) {
 
 

      //Cette ligne de code ajoutera le nom du commune à un popup, qui s'ouvrira lorsque l'utilisateur cliquera sur la limite du quartier sur la carte
          //var nom_commune = JSON.stringify(json.features[0].properties.nom_commune);
         //limite_commune.bindPopup(nom_commune);


 // 2. Vérifier la réponse brute du serveur
           console.log('Réponse brute de zoom_commune pour ID ' + id_commune + ':', xhr_zoom.responseText);
        try {
            var json_commune = JSON.parse(xhr_zoom.responseText);

                if (json_commune.error) {
                    console.error('Erreur AJAX signalée par PHP (zoom):', json_commune.error);
                    alert('Erreur lors du zoom: ' + json_commune.error);
                    return; // Arrêter si une erreur est renvoyée par le serveur
                }

                // 3. Vérifier si des fonctionnalités GeoJSON sont présentes
                
   
                 // --- NOUVEAU CODE À AJOUTER ---
                   if (json_commune && json_commune.features && json_commune.features.length > 0) {
                    var firstFeature = json_commune.features[0];
                    if (!firstFeature.geometry || firstFeature.geometry.coordinates.length === 0) {
                        console.warn('La géométrie de la première fonctionnalité est vide ou manquante pour ID ' + id_commune);
                        // alert('La géométrie de la commune est vide ou invalide.'); // Laissez ceci commenté
                        return;
                    }
                    // --- FIN NOUVEAU CODE ---
 
                  
                  
                  // 4. Ajouter les données à la couche `limite_commune`
                    limite_commune.addData(json_commune);

                    // 5. Ajuster le zoom de la carte aux limites de la géométrie reçue
                    // Vérifiez que limite_commune a bien des limites après l'ajout des données
                    if (limite_commune.getBounds().isValid()) {
                        carte.fitBounds(limite_commune.getBounds());
                        console.log('Zoom effectué sur la commune ID:', id_commune);

                        // Optionnel : Ajouter un popup avec le nom de la commune
                       // var nom_commune = json.features[0].properties.nom_commune;
                        //limite_commune.bindPopup(nom_commune).openPopup();

                      
                        // L'appel AJAX pour les statistiques de risques
                        var url_stats = 'ajax.php?elemid=information_commune&id_commune=' + id_commune;
                        var xhr_stats = getXhr();

                        xhr_stats.onreadystatechange = function() {
                            if (xhr_stats.readyState == 4) {
                                console.log('Réponse brute des stats pour ID ' + id_commune + ':', xhr_stats.responseText);
                                try {
                                    var stats = JSON.parse(xhr_stats.responseText);

                                    if (stats.error) {
                                        console.error('Erreur AJAX signalée par PHP (stats):', stats.error);
                                        // Optionnel: Afficher un message d'erreur dans les onglets ou laisser 0
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
                                  //  document.getElementById('nombre_risque').textContent = stats.tt_risque !== undefined ? stats.tt_risque : 'N/A';
                                    document.getElementById('nombre_risque').textContent = stats.tt_risques !== undefined ? stats.tt_risques : '0'; // Utilisez tt_risques, et '0' par défaut
                                    // --- FIN DE LA MISE À JOUR ---

                                } catch (e) {
                                    console.error('Erreur de parsing JSON pour les statistiques:', e);
                                    // Optionnel: Afficher un message d'erreur dans les onglets ou laisser 0
                                    document.getElementById('nombre_inondation').textContent = 'Erreur';
                                    document.getElementById('nombre_erosion').textContent = 'Erreur';
                                    document.getElementById('nombre_eboulement').textContent = 'Erreur';
                                    document.getElementById('nombre_effondrement').textContent = 'Erreur';
                                    document.getElementById('nombre_risque').textContent = 'Erreur';
                                }
                            }
                             };
                            xhr_stats.open("GET", url_stats, true);
                        xhr_stats.send(null);

                    } else {
                        console.warn('La couche limite_commune n\'a pas de limites valides pour zoomer, même après l\'ajout de données.');
                    }
                } else {
                     console.warn('Aucune donnée GeoJSON valide ou aucune fonctionnalité trouvée pour la commune ID ' + id_commune);
                    alert('Impossible de trouver la géométrie pour cette commune.');
                }
            } catch (e) {
                console.error('Erreur de parsing JSON pour zoom_commune:', e);
                alert('Erreur lors du traitement des données de la commune. Vérifiez la console.');
            }
        }
    };
       xhr_zoom.open("GET", url_zoom, true);
    xhr_zoom.send(null);
    //xhr.open("GET", url, true);
   // xhr.send(null);
}

// --- Chargement initial des couches (Après la définition des couches et la fonction getXhr) ---

$(document).ready(function() {
    // Correction : Charger les données de la couche principale
    // Et assurez-vous que les autres couches chargent leurs propres fichiers
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

});



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

    <script>

    </script>
 
  </body>
</html>