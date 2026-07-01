

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

/*contenant du navigateur deroulant*/
    #wrap {
        width: 40%;
        height: 50px;
        margin: auto;
        z-index: 99;
        position: relative;
        float:right;
      }
      .navbar {
        height: 50px;
      
        padding: 0;
        margin: 0;
        position: absolute;
      }
      .navbar li {
        height: auto;
        width: 135.8px;
        float: left;
        text-align: center;
        list-style: none;
        font: normal bold 13px/1em Arial, Verdana, Helvetica;
        padding: 0;
        margin: 0;
        background-color:#083a6f;
      }
      .navbar a {
        padding: 18px 0;
        border-left: 1px solid #ccc9c9;
        text-decoration: none;
        color: white;
        display: block;
      }
      .navbar li:hover,
      a:hover {
        background-color: #444444;
      }
      .navbar li ul {
        display: none;
        height: auto;
        margin: 0;
        padding: 0;
      }
      .navbar li:hover ul {
        display: block;
      }
      .navbar li ul li {
        background-color: #444444;
      }
      .navbar li ul li a {
        border-left: 1px solid #444444;
        border-right: 1px solid #444444;
        border-top: 1px solid #c9d4d8;
        border-bottom: 1px solid #444444;
      }
      .navbar li ul li a:hover {
        background-color: #a3a1a1;
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
              <a href="#">
                <ul class="treeview-menu">
                <li class="active"><a href="index.php"><i class="fa fa-circle-o"></i> Acceuil </a></li>
              </a>
              </li>
            
            <li class="treeview">
              <a href="documentation/index.html">
                <i class="fa fa-pie-chart"></i>
                <span>Prévision</span>
            </li>

            <li class="treeview"><a href="documentation/index.php"><i class="fa fa-book"></i> <span>Résume de these</span></a></li>
        

            <li class="treeview">
              <a href="#">
                <i class="fa fa-share"></i> <span>Phototheque</span>
                <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
               
                <li>
                  <a href="#"><i class="fa fa-circle-o"></i> Vidéos <i class="fa fa-angle-left pull-right"></i></a>
                  <ul class="treeview-menu">
                  
                    <li>
                      <a href="#"><i class="fa fa-circle-o"></i> Années <i class="fa fa-angle-left pull-right"></i></a>
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
          </ul>
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
       
        </nav>
   

    <div id="wrap">
      <ul class="navbar">
        
        <li>
          <a href="#">Quartiers</a>
          <ul>
            <li><a href="#">Akouédo Ancien</a></li>
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
            li><a href="#">Djibi</a></li>
          </ul>
        </li>
        
        <li>
          <a href="#">Zones</a>
          <ul>
            <li><a href="#">CSS de Base</a></li>
            <li><a href="#">PHP de Base</a></li>
            <li><a href="#">JavaScript de Base</a></li>
          </ul>
        </li> 
     
      </ul>
      
      </div>
</div>

<!--header-search-box-->

</div>
<!--container-->

</div>
<!--main-nav-->






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










poster un document





<?php
// connection à la base données
  function connect() {
    $conn = null;

    try{
      $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','0151516084'); // On se connecte à la base de donnée
    } 
    // S'il existe un problème de connection, on obtient le message d'erreur
    catch(PDOException $ex) {
      echo 'Connection error : ' . $ex->getMessage(); 
    }
    return $conn;
  }


//$conn=connect();
//$liste_commune="";
//Définition de la requête SQL
   //$sql = 'SELECT id, "commune" as nom_commune, "id" FROM public.communes_abidjan';

//Executer la requete
   // $result = $conn->query($sql);


    //Parcourir la table des résultats de la requête
	//while($row = $result->fetch(PDO::FETCH_ASSOC)){
    //affiche le contenu de la table
  //  $liste_commune.= '<li><a href="#" onclick="zoomCommune(\''.$row['id'].'\');">'.$row['nom_commune'].'</a></li>';
//	}     

//Insertion des articles dans la base de données (PHP)
?>





<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Poster un artcle</title>
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
    <link rel="stylesheet" href="style.css">



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
			  width:50%;/*largueur 75% du parent*/
        max-width:2000px;	/*largueur maximal de boite*/
        box-shadow: 0 0 15px /*(0,0,0,3) /*opatiter de la boite*/ ;
        display: flex;
        align-items: center; /*centrage horizontal entre le texte et la carte*/
        font-size: 18px; /*taille*/  
        background-color: white;  /*fond du couleur*/
        border-radius: 15px  /*forme de la bordure de la boite*/;

		  }
    
 /*position des cadres en dessous des textes*/
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

     /*largeur et centrer du texte*/
      form {
          padding:30px 60px;     
		  }
    
    

    </style>


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="skin-blue fixed" data-spy="scroll" data-target="#scrollspy">
    <div>

      <header class="main-header">
        <!-- Logo -->
        <!-- Logo -->
        <a href="../documentation/index.php" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>A</b>LT</span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>Documentation</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
         <span class="txt"> Postez vos articles, thése, masters etc.
         </span> 
          
        </nav>
        
      </header>
    </div>
    

    <div class="contener">
    
<!-- Creer un bouton pour envoyer les fichiers-->

      <form id="articleForm" action="#" method="post">
      

           
               
       
               
      <!-- <div>
				<label for="commune">commune:</label>
				<input type="text"  name="commune" id="commune" placeholder="commune"/>
			</div> -->
         <div class="form-group">
				<label for="nom">Nom de l'auteur:</label>
				<input type="text" name="nom" id="nom" placeholder="nom"/>
        
			</div> 

             
      <div class="form-group">
				<label for="prenom">Prénom:</label>
				<input type="text" name="prenom" id="prenom" placeholder="prenom"/>
			</div> 

      <div class="form-group">
				<label for="titre">Titre du document:</label>
				<input type="text" name="titre" id="titre" placeholder="titre"/>
			</div> 
               
        <div class="form-group">
					<label for="content">Résume du document</label>
					<textarea name="content" id="content" rows="5" cols="60"></textarea>

				</div>
				<div >
					<input type="file" class= "form-control-file" id="choisir_des_fichiers">
					<p class="text"> Types de fichiers autorisés: .xls .doc .docx .pdf .odt txt .jpg .png</p>
				</div>
				<div>
       
					<!--Creer un bouton pour envoyer les fichiers-->
					<input type="submit"  name="submit" value="Envoyer" id="bouton_envoie" class="form-control"  style="width:auto;"/>
				</div>
      
      </form>
  
      <!--Importer le cadre de la carte-->
		<div id="mapid"></div>

        </div> 
 

  <script>        

        // nous ajoutons du JavaScript pour valider le formulaire côté client avant qu'il ne soit soumis.					
    
         document.getElementById('articleForm').addEventListener('submit', function(event) {
        const nom = document.getElementById('nom').value.trim();
        const prenom = document.getElementById('prenom').value.trim();
        const titre = document.getElementById('titre').value.trim();
        const content = document.getElementById('content').value.trim();

        if (nom === '' || prenom === '' || titre === '' || content === '') {
        alert("Veuillez remplir tous les champs.");
        event.preventDefault(); // Empêche l'envoi du formulaire
        }
    });
  


       
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






// formulaire pour postuler un document (post_article)


<?php
// Connexion à la base de données PostgreSQL
$host = 'localhost';
$dbname = 'base_inondation';
$user = 'postgres'; // Utilisateur PostgreSQL
$password = '0151516084'; // Mot de passe PostgreSQL

// Connexion PDO
try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("La connexion a échoué : " . $e->getMessage());
}

// Ajouter une photo si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image'])) {
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $image = $_FILES['image'];

    // Nom de l'image et chemin
    $imageName = basename($image['name']);
    $targetDir = "photos/";
    $targetFile = $targetDir . $imageName;

    // Vérification si c'est une image
    if (getimagesize($image['tmp_name'])) {
        if (move_uploaded_file($image['tmp_name'], $targetFile)) {
            // Ajouter dans la base de données
            $stmt = $conn->prepare("INSERT INTO photos (titre, description, chemin_image) VALUES (?, ?, ?)");
            $stmt->execute([$titre, $description, $imageName]);
            echo "<div class='alert alert-success'>Photo ajoutée avec succès! <a href='gallery.php'>Voir la galerie</a></div>";
        } else {
            echo "<div class='alert alert-danger'>Désolé, il y a eu une erreur lors du téléchargement de l'image.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Ce n'est pas une image valide!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--title--> <!--/title-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
   


    
      <!-- Ionicons -->
      <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="style.css">
   
   <style>
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
			  width:50%;/*largueur 75% du parent*/
        max-width:2000px;	/*largueur maximal de boite*/
        box-shadow: 0 0 15px /*(0,0,0,3) /*opatiter de la boite*/ ;
        display: flex;
        align-items: center; /*centrage horizontal entre le texte et la carte*/
        font-size: 18px; /*taille*/  
        background-color: white;  /*fond du couleur*/
        border-radius: 15px  /*forme de la bordure de la boite*/;

		  }
    
 /*position des cadres en dessous des textes*/
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

     /*largeur et centrer du texte*/
      form {
          padding:30px 60px;     
		  }
    
    

    

    </style>
</head>
<body class="skin-blue fixed" data-spy="scroll" data-target="#scrollspy">

<div>
<header class="main-header">
        <!-- Logo -->
        <!-- Logo -->
        <a href="../documentation/index.php" class="logo">
        
          
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>Documentation</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
         <span class="txt"> Postez vos articles, thése, masters etc.
         </span> 
          
        </nav>
        
      </header>
    </div>



<div class="contener">

<form id="articleForm" action="#" method="post">

    <div class="section">

            <div class="form-group">
				      <label for="nom">Nom de l'auteur:</label>
				      <input type="text" class="form-control" id="nom" name="nom" required>
        
			      </div> 

            <div class="form-group">
				        <label for="prenom">Prénom de l'auteur:</label>
				        <input type="text" class="form-control" id="prenom" placeholder="prenom" required/>
			      </div> 
       
            <div class="form-group">
                <label for="titre" class="form-label">Titre du document</label>
                <input type="text" class="form-control" id="titre" name="titre">
            </div>
            <div class="form-group">
                <label for="description" class="form-label">Résume du document</label>
                <textarea name="content" id="content" rows="5" cols="60"></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Choisir un document</label>
                <input type="file" class="form-control" id="image" name="image" required>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter la photo</button>
        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>




// formulaire pour afficher les documents

<?php
// Connexion à la base de données PostgreSQL
$host = 'localhost';
$dbname = 'base_inondation';
$user = 'postgres'; // Utilisateur PostgreSQL
$password = '0151516084'; // Mot de passe PostgreSQL

// Connexion PDO
try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("La connexion a échoué : " . $e->getMessage());
}

// Récupérer toutes les photos depuis la base de données
$stmt = $conn->prepare("SELECT * FROM photos ORDER BY date_ajout DESC");
$stmt->execute();
$photos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie des Photos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .photo-container {
            margin-top: 20px;
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center my-4">Galerie des Photos</h2>

    <div class="row photo-container">
        <?php foreach ($photos as $photo): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="photo/<?php echo htmlspecialchars($photo['chemin_image']); ?>" class="card-img-top" alt="Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($photo['titre']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($photo['description']); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>







// formulaire affichez un document 
<?php
// connection à la base données
  function connect() {
    $conn = null;

    try{
      $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','0151516084'); // On se connecte à la base de donnée
    } 
    // S'il existe un problème de connection, on obtient le message d'erreur
    catch(PDOException $ex) {
      echo 'Connection error : ' . $ex->getMessage(); 
    }
    return $conn;
  }


//$conn=connect();
//$liste_commune="";
//Définition de la requête SQL
   //$sql = 'SELECT id, "commune" as nom_commune, "id" FROM public.communes_abidjan';

//Executer la requete
   // $result = $conn->query($sql);


    //Parcourir la table des résultats de la requête
	//while($row = $result->fetch(PDO::FETCH_ASSOC)){
    //affiche le contenu de la table
  //  $liste_commune.= '<li><a href="#" onclick="zoomCommune(\''.$row['id'].'\');">'.$row['nom_commune'].'</a></li>';
//	}     

//Insertion des articles dans la base de données (PHP)
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
    <link rel="stylesheet" href="style.css">



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
			  width:50%;/*largueur 75% du parent*/
        max-width:2000px;	/*largueur maximal de boite*/
        box-shadow: 0 0 15px; /*(0,0,0,3) /*opatiter de la boite*/ 
        align-items: center; /*centrage horizontal entre le texte et la carte*/
        font-size: 18px; /*taille*/  
        background-color: white;  /*fond du couleur*/
        border-radius: 15px;  /*forme de la bordure de la boite*/
		  }
    
      /*position des cadres en dessous des textes*/
      .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
      }

      /*largeur et centrer du texte*/
      form {
        padding:30px 60px;     
		  }
      
      .download-btn {
        display: left;
      }

    </style>


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="skin-blue fixed" data-spy="scroll" data-target="#scrollspy">
    <div>

      <header class="main-header">
        <!-- Logo -->
        <!-- Logo -->
        <a href="../documentation/index.php" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>A</b>LT</span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>Documentation</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
         <span class="txt"> Télecharger vos articles, thése, masters etc.
         </span> 
          
        </nav>
        
      </header>
    </div>
    

    <div class="contener">
    
<!-- Creer un bouton pour envoyer les fichiers-->

      <form id="articleForm" action="#" method="post"> 
               
      <!-- <div>
				<label for="commune">commune:</label>
				<input type="text"  name="commune" id="commune" placeholder="commune"/>
			</div> -->
        

      <div class="form-group">
				<label for="titre">Titre du document:</label>
				<input type="text" name="titre" id="titre" placeholder="titre"/>
			</div> 
               
      <div class="form-group">
					<label for="content">Résume du document</label>
					<textarea name="content" id="content" rows="5" cols="60"></textarea>

			</div>

      <div class="container">
          <label for="auteur">Auteur</label>
          <input type="text" name="auteur" id="auteur" placeholder="auteur"/>
          <!-- Bouton de téléchargement aligné à gauche -->
          <a href="download.php?file=mon_fichier.pdf" class="download-btn">Télécharger le fichier</a>
      </div>
    </form>

    <script>        

        // nous ajoutons du JavaScript pour valider le formulaire côté client avant qu'il ne soit soumis.					
    
         document.getElementById('articleForm').addEventListener('submit', function(event) {
        const nom = document.getElementById('nom').value.trim();
        const prenom = document.getElementById('prenom').value.trim();
        const titre = document.getElementById('titre').value.trim();
        const content = document.getElementById('content').value.trim();
        if (nom === '' || prenom === '' || titre === '' || content === '') {
        alert("Veuillez remplir tous les champs.");
        event.preventDefault(); // Empêche l'envoi du formulaire
        }
      });
  


       
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
