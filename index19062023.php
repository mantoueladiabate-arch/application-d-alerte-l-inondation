<?php
// connection à la base données
  function connect() {
    $conn = null;

    try{
      $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','postgres');
    } 
    // S'il existe un problème de connection, on obtient le message d'erreur
    catch(PDOException $ex) {
      echo 'Connection error : ' . $ex->getMessage(); 
    }
    return $conn;
  }


$conn=connect();
   $liste_quartier="";
//Définition de la requête SQL
   $sql = 'SELECT id, "area", "commune", "nom_quar" as nom_quartier, "id" FROM public.quartiers_cocody';

//Executer la requete
    $result = $conn->query($sql);


    //Parcourir la table des résultats de la requête
	while($row = $result->fetch(PDO::FETCH_ASSOC)){
    $liste_quartier.= '<li><a href="#" onclick="zoomQuartier(\''.$row['id'].'\');">'.$row['nom_quartier'].'</a></li>';
	}     

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

      /*la carte*/
      #mapid {
        border : 0px solid green;
			  width: 100%; /*largueur de l'image*/
			  height:700px; /*hauteur de l'image*/
			  margin: auto;
			  float:right; 
		   } 

       /*logo*/
    
       img {
        width: 100%; /*largueur de l'image*/
			  height:100px; /*hauteur de l'image*/
			 
      }
		</style>
		




    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

  

    <![endif]-->
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
            
            <img src="data/institutgeographietropicale.jpeg"  alt="logo de l'igt"/>
           
           </div>
          
         <!-- search form -->
          <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
             <input type="text" name="q" class="form-control" placeholder="Search...">
               <span class="input-group-btn">
                 <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
               </span>
            </div>
          </form>
         
         
          <!-- /.search form -->
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
            
            <li class="active treeview">
              <a href="#"><li class="active"><a href="index.php"><i class="fa fa-circle-o"></i> Acceuil </a></li></a>
            </li>
            
            <li class="treeview">
              <a href="documentation/index.html"><i class="fa fa-pie-chart"></i><span>Prévision</span></a>
            </li>

            <li class="treeview">
              <a href="documentation/index.php"><i class="fa fa-book"></i> <span>Résume de these</span></a>
            </li>
        

            <li class="treeview">
              <a href="#"> <i class="fa fa-share"></i> <span>Phototheque</span><i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Vidéos <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                  
                    <li>
                      <a href="#"><i class="fa fa-circle-o"></i>Années<i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                          <li><a href="pages/tables/simple.html"><i class="fa fa-circle-o"></i> 2021</a></li>
                          <li><a href="pages/tables/simple.html"><i class="fa fa-circle-o"></i> 2020</a></li>
                          <li><a href="pages/tables/simple.html"><i class="fa fa-circle-o"></i> 2019</a></li>
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
                        <li><a href="#"><i class="fa fa-circle-o"></i> 2021</a></li>
                        <li><a href="#"><i class="fa fa-circle-o"></i> 2020</a></li>
                        <li><a href="#"><i class="fa fa-circle-o"></i> 2019</a></li>
                      </ul>
                    </li>
                  </ul>
                </li>
              </ul>
            </li>

            <li class="treeview">
              <a href="#"><i class="fa fa-circle-o"></i><span>Quartiers</span><i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                <?php echo $liste_quartier; ?>

                  <!-- <li><a href="#">Akouédo Ancien</a></li>
                  <li><a href="#"> Djorogobité</a></li>
                  <li><a href="#"> Angré</a></li>
                  <li><a href="#">Plateau Dokui</a></li>
                  <li><a href="#">Aghien</a></li>
                  <li><a href="#">Deux Plateaux</a></li>
                  <li><a href="#">Riviera 2</a></li>
                  <li><a href="#">Rviera 3</a></li>
                  <li><a href="#">Riviera Bonoumin</a></li>
                  <li><a href="#">7éme Tranche</a></li>
                  <li><a href="#">9éme Tranche</a></li>
                  <li><a href="#">vieux Cocodyi</a></li>
                  <li><a href="#">Ambassade</a></li>
                  <li><a href="#">Deux Plateaux</a></li>
                  <li><a href="#">Blockauss</a></li>
                  <li><a href="#">M'Pouto</a></li>
                  <li><a href="#">Djibi</a></li> -->
                </ul> 
            </li> 

            <li class="treeview">
              <a href="#"><i class="fa fa-circle-o"></i> <span>Zones</span><i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                  <li><a href="#">CSS de Base</a></li>
                  <li><a href="#">PHP de Base</a></li>
                  <li><a href="#">JavaScript de Base</a></li>
                </ul>
            </li>         
          </ul>
        
          
            <!-- <li><a href="#">Akouédo Ancien</a></li>
            <li><a href="#">Djorogobité</a></li>
            <li><a href="#">Angré</a></li>
            <li><a href="#">Plaeau Dokui</a></li>
            <li><a href="#">Aghien</a></li>
            <li><a href="#">Deux Plateaux</a></li>
            <li><a href="#">Riviera 2</a></li>
            <li><a href="#">Rviera 3</a></li>
            <li><a href="#">Riviera Bonoumin</a></li>
            <li><a href="#">7éme Tranche</a></li>
            <li><a href="#">9éme Tranche</a></li>
            <li><a href="#">vieux Cocodyi</a></li>
            <li><a href="#">Ambassade</a></li>
            <li><a href="#">Deux Plateaux</a></li>
            <li><a href="#">Blockauss</a></li>
            <li><a href="#">M'Pouto</a></li>
            li><a href="#">Djibi</a></li> -->
    </section>
        <!-- /.sidebar -->
  </aside>
 </div>


  <div id='contenu'><h2>PREVISION DES RISQUES D'INONDATION</h2></div>
    <div id="main-nav" class="clear-fix"> 
      <div class="container">
        <nav id="site-navigation" class="main-navigation"role="navigation">
          <div class="wrap-menu-content">
            <div class="menu-menu-fr-container">
            <!--wrap-menu-content-->
            </div>
          <!--header-search-box-->      
          </div>
        </nav>
      <!--container-->
      </div>
    </div>
  <!--main-nav-->
  </div>


      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
      
      <!-- Main content -->
      <section class="content">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
              <div class="inner">
                <h3>150</h3>
                <p>Nombre de zones impactées</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
            </div>
          </div><!-- ./col -->
          <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
              <div class="inner">
                <h3>53</h3>
                <p>Population à risque</p>
              </div>
              
              <div class="icon">
                <i class="ion ion-person"></i>
              </div>
            </div>
          </div><!-- ./col -->
          <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
              <div class="inner">
                <h3>44<sup style="font-size: 20px">%</sup></h3>
                <p>Homme impacté</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
            </div>
          </div><!-- ./col -->
          <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
              <div class="inner">
                <h3>65<sup style="font-size: 20px">%</sup></h3>
                <p>Femme impactée</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
            </div>
          </div><!-- ./col -->
        </div><!-- /.row -->
        


     <!--Importer le cadre de la carte-->
		<div id="mapid"></div>

    
    <script>

    // Initialiser la carte//
      var carte = L.map('mapid', { 
      center: [5.3294815, -3.9919594], 
      zoom: 13
       });


    // caracteristique des couches
      var limite_quartier = L.geoJson(null,{
				style: function(feature){
					return {
					color: 'yellow',
					weight: 2,
					fill: true,
					fillColor: 'gray',
					fillOpacity: 0.5
							}
						}
					});


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
        
    	// Création outils à droite en haut
			var layerControl = L.control.layers(fonds);
				
				// Ajouter à la carte
			carte.addControl(layerControl);

        //Ajouter l'echelle cartographique
      L.control.scale().addTo(carte);

        //Ajouter les couches
      function zoomQuartier(id_quartier) { 
   

        limite_quartier.clearLayers();
        //liaison avec le fichier ajax
        var url = 'ajax.php?elemid=zoom_quartier&id_quartier='+id_quartier;
        var xhr = getXhr();

       // récupérer la réponse
      xhr.onreadystatechange = function() {
        if(xhr.readyState == 4) {
          retour = xhr.responseText;

      //convertir JSON en un objet JavaScript
      var json = JSON.parse(retour);
              //ajouter les données à la couche
        limite_quartier.addData(json); 
              //Ajouter la couche à la carte 
        limite_quartier.addTo(carte); 

              // alert(json.features);
      //Cette ligne de code ajoutera le nom du quartier à un popup, qui s'ouvrira lorsque l'utilisateur cliquera sur la limite du quartier sur la carte
          var nom_quartier = JSON.stringify(json.features[0].properties.nom_quartier);
          limite_quartier.bindPopup(nom_quartier);

          carte.fitBounds(limite_quartier.getBounds()); 
            }
          }

          xhr.open("GET",url,true);
          xhr.send(null);
        }
			
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
