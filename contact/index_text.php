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
        *police et couleur du texte*/
        body {
			font-family: 'Tw Can MT', sans-serif;	
            color: black;

		}

        *largeur et centrer du texte*/
        form {
			max-width:600px;	
            margin:10px auto;
            padding:10px 20px;

		}
       form {
			max-width:600px;	
            margin:0 0 30px 0;
           text-align; center;

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
    <div class="wrapper">

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
          
          
        </nav>
      </header>
    </div>

      <form action="">
      <h1> Aider nous à rendre nos donnée plus precis.Fournissez-nous des données sur votre commune et quartier  </h1>
        
        <fieldset>  
             
				<label for="Commune">Commune:</label>
				<input type="text" name="commune" id="Commune" placeholder="commune"/>
			
            <div >
				<label for="quartier">Quartier:</label>
				<input type="text"  name="quartier" id="quartier" placeholder="quartier"/>
			</div>
            <div >
				<label for="sexe">Homme</label>
				<input type="radio" name="sexe" id="homme"/>
				<label for="sexe">Femme</label>
				<input type="radio" name="sexe" id="femme" placeholder="Entrez votre nom"/>
			</div>
            <label for="age">Tranche d'age:</label>
            <select name="" id="age">
             
             <option value="age">14 à 21</option> 
             <option value="age">21 à 35</option>
             <option value="age">35 à 49</option>
             <option value="age">49 à 56</option>
             <option value="age">56 et plus</option>
             
            </select>


            <div >
					<label for="observation">Observation</label>
					<textarea name="observation" id="observation" rows="3"></textarea>
				</div>
				<div >
					<input type="file" class= "form-control-file" id="choisir_des_fichiers">
					<p class="text"> Types de fichiers autorisés: .xls .doc .docx .pdf .odt txt .jpg .png</p>
				</div>
				<div>
					<!--Creer un bouton pour envoyer les fichiers-->
					<input type="submit"  name="submit" value="Envoyer" id="bouton_envoie" class="form-control"  style="width:auto;"/>
				</div>
        </fieldset>
      </form>



      
    <!-- jQuery 2.1.4 -->
    <script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="../bootstrap/js/bootstrap.min.js"></script>
    <!-- FastClick -->
    <script src="../plugins/fastclick/fastclick.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/app.min.js"></script>
    <!-- SlimScroll 1.3.0 -->
    <script src="../plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js"></script>
    <script src="docs.js"></script>
  </body>
</html>
