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

      </head>

      <body>
      	<!-- Navigation -->
           
      <header class="main-header">
        <!-- Logo -->
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
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          
        </nav>
      </header>
			<!--image de fond-->	
			<body background="images/eau.jpeg">
        <div class="row">
          <div class="col-md-6">
			  
          </div>
		<div class="col-md-6">       
			<!--Creer un formulaire-->
			<!--post indique la facon dont les données seront envoyées, action est l'adresse de la page quiva recuperer les données et les enregistrer-->
			<form method="post" action="test-traitement.php" enctype="multipart/form-data">
				 <div id="formulaire">  
				  <p>Aider nous à rendre nos donnée plus precis.<br/> Fournissez-nous des données sur votre commune/quartier</p>  

				<div class="form-group">
					<label for="prenoms">Nom *</label>
					<input type="text" class="form-control" name="nom" id="nom" placeholder="Entrez vos nom">
				</div>
  
				<div class="form-group mt-3">
					<label for="prenoms">Prénoms *</label>
					<input type="text" class="form-control" name="prenoms" id="prenoms" placeholder="Entrez vos prenoms">
				</div>
				<div class="form-group mt-3">
					<label form="sexe">Homme</label>
					<input type="radio" name="sexe" id="homme"/>
					<label form="sexe">Femme</label>
					<input type="radio" name="sexe" id="femme" placeholder="Entrez votre nom"/>
				</div>
			    <div class="form-group mt-3">
					<label form="email">Adresse Email</label>
					<input type="email" class="form-control" name="email" id="email" placeholder="Entrez votre Email"/>
				</div>
				<div class="form-group mt-3">
					<label form="contact">Numéro de téléphone</label>
					<input type="tel" class="form-control" name="contact" id="contact" placeholder="Entrez votre contact"/>
				</div>
				<div class="form-group mt-3">
					<label form="origine">Commune/quartier</label>
					<input type="text" class="form-control" name="origine" id="origine" placeholder="Commune/quartier"/>
				</div>
				<div class="form-group mt-3">
					<label form="commentaire">Commentaire</label>
					<textarea class="form-control" name="commentaire" id="commentaire" rows="3"></textarea>
				</div>
				<div class="form-group mt-3">
					<input type="file" class= "form-control-file" id="choisir_des_fichiers">
					<p class="text"> Types de fichiers autorisés: .xls .doc .docx .pdf .odt txt .jpg .png</p>
				</div>
				<div>
					<!--Creer un bouton pour envoyer les fichiers-->
					<input type="submit"  name="submit" value="Envoyer" id="bouton_envoie" class="form-control"  style="width:auto;"/>
				</div>
				</div>
				</div>
			</form>
		</div>

	

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