<?php
require_once __DIR__ . '/../config.php';
// connection à la base données
  function connect() {
    $conn = null;

    try{
      $conn = new PDO(DB_DSN, DB_USER, DB_PASS); // On se connecte à la base de donnée
    } 
    // S'il existe un problème de connection, on obtient le message d'erreur
    catch(PDOException $ex) {
      echo 'Connection error : ' . $ex->getMessage(); 
    }
    return $conn;
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




