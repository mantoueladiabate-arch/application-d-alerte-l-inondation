<?php
// Démarrer la session au tout début du fichier
session_start();
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

/* --- Nouveaux styles pour la page d'accueil --- */
body {
margin: 0;
font-family: sans-serif;
}
 .home-page {
        position: relative;
        width: 100%;
        height: 100vh; /* Prend toute la hauteur de la fenêtre */
       /*mettre l'image en fond*/
        background-image:url('images/hotel ivoire octobre 2024.jpg');
       /* background-image:url('images/inondations.jng'); /* REMPLACEZ */
        background-size: cover;
        background-position: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: #fff;
        text-align: center;
    }

    .overlay-text {
        background-color: rgba(0, 0, 0, 0.5);
        padding: 5px;/*la largeur du cadre*/
        border-radius: 10px;/*la bordure du cadre*/
        margin-bottom: 20px;
        position: absolute;
        top: 0; /*hauteur*/
        left: 50;/*a gauche*/
    }

    .overlay-text h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
    }

    .overlay-text p {
        font-size: 1.2em;
        margin-top: 0;
    }

    .login-container {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .login-button {
        padding: 10px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
    }

    .faq-section {
        padding: 50px;
        background-color: #f4f4f4;
        text-align: left;
    }

    .faq-item {
        margin-bottom: 20px;
    }

    .faq-item h3 {
        font-size: 1.4em;
        color: #333;
    }

    .faq-item p {
        font-size: 1.1em;
        line-height: 1.6;
        color: #555;
    }

    /* Styles pour les formulaires de connexion/inscription (à afficher via JS) */
    .modal {
        display: none; /* Masqué par défaut */
        position: fixed; /* Restez en place */
        z-index: 1; /* Au-dessus des autres éléments */
        left: 0;
        top: 0;
        width: 100%; /* Pleine largeur */
        height: 100%; /* Pleine hauteur */
        overflow: auto; /* Activer le défilement si nécessaire */
        background-color: rgba(0,0,0,0.4); /* Fond semi-transparent noir */
    }

   .modal-content {
    background-color: #fefefe;
    margin: 10% auto; /* 10% vers le bas et centré horizontalement */
    padding: 20px;
    border: 1px solid #888;
    width: 90%; /* Prend 90% de la largeur sur les petits écrans */
    max-width: 400px; /* Mais ne dépasse jamais 400px de large */
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Ajoute une ombre pour un effet de pop-up */
}

    .close-button {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close-button:hover,
    .close-button:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .form-actions button {
        background-color: #007bff;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .form-actions a {
        display: block;
        margin-top: 10px;
        color: #007bff;
        text-decoration: none;
    }



.menu {
  flex: 1;
 
  color: white;
  padding: 20px;
   min-width: 200px;

}

 .faq-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        padding: 15px;
        background-color: #eee;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .faq-toggle:hover {
        background-color: #ddd;
    }

    .faq-toggle h3 {
        margin: 0;
        font-size: 1.4em;
    }
    
    .faq-toggle span {
        transition: transform 0.3s ease;
    }

    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, padding 0.3s ease;
        padding: 0 15px;
        background-color: #f9f9f9;
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
    }

    .faq-answer.show {
        max-height: 200px; /* Une valeur assez grande pour contenir la réponse */
        padding: 15px;
    }

    .faq-toggle.active span {
        transform: rotate(90deg);
    }
		</style>
	
  </head>

<body >
  
    <div class="menu">

 <div class="hold-transition skin-blue sidebar-mini">
 
  <a href="../index.php" class="logo">
 
      <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
          <!-- Sidebar user panel -->
          
         <!-- search form -->
          <form action="#"method="get" class="sidebar-form">
            <div class="input-group">
             <input type="text"name="q" class="form-control" placeholder="Search...">
               <span class="input-group-btn">
                 <button type="submit"name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
               </span>
            </div>
          </form>
         
        
          <ul class="sidebar-menu">
            
            <li class="active treeview">
              <a href="#"><li class="active"><a href="index.php"><i class="fa fa-circle-o"></i> Accueil </a></li></a>
            </li>
            
          <li class="treeview">
              <a href="tableaubord/index.php"> <i class="fa fa-table"></i> <span>tableau de bord</span></i></a>
            </li>  
             <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'analyste' || $_SESSION['role'] === 'administrateur')): ?>
             <li class="treeview">
              <a href="statistique/index.php"> <i class="fa fa-table"></i> <span>Statistique</span></i></a>
            </li>  
              <?php endif; ?>

            <li class="treeview">
              <a href="contact/index.php"><i class="fa fa-stop"></i><span>Emmetre une alerte</span></a>
            </li>

            
             <li class="treeview">
            <a href="analyste/index.php"><i class="fa fa-search"></i><span>Analystes</span></a>
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

</div>
 

<div class="home-page">
<div class="login-container">
    
      <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
<i>  
 <a href="logout.php" class="login-button">Se déconnecter</a>
        <?php else: ?>
            <a href="#" class="login-button" onclick="document.getElementById('login-modal').style.display='block'">Se connecter</a>
        <?php endif; ?>
</div>

<div class="overlay-text">
<h1>Alertes Inondation</h1>
<p>Protection active contre les inondations, votre sécurité avant tout.</p>
</div>
</div>



   <div id="login-modal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="document.getElementById('login-modal').style.display='none'">&times;</span>
        <h2>Connexion</h2>
        <div id="login-form">
            <form action="process_login.php" method="post">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-actions">
                    <button type="submit">Se connecter</button>
                    <a href="#" onclick="showForgotPasswordForm()">Mot de passe oublié?</a>
                    <a href="#" onclick="showRegistrationForm()">Première connexion? S'inscrire</a>
                </div>
            </form>
        </div>

        <div id="registration-form" style="display: none;">
            <h2>Inscription</h2>
            <form action="process_register.php" method="post">
                <div class="form-group">
                    <label for="reg_username">Nom d'utilisateur:</label>
                    <input type="text" id="reg_username" name="reg_username" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">Mot de passe:</label>
                    <input type="password" id="reg_password" name="reg_password" required>
                </div>
                <div class="form-group">
                    <label for="reg_confirm_password">Confirmer le mot de passe:</label>
                    <input type="password" id="reg_confirm_password" name="reg_confirm_password" required>
                </div>
                <div class="form-actions">
                    <button type="submit">S'inscrire</button>
                    <a href="#" onclick="showLoginForm()">Déjà un compte? Se connecter</a>
                </div>
            </form>
        </div>

        <div id="forgot-password-form" style="display: none;">
            <h2>Mot de passe oublié</h2>
            <form action="process_forgot_password.php" method="post">
                <div class="form-group">
                    <label for="reset_username">Nom d'utilisateur:</label>
                    <input type="text" id="reset_username" name="reset_username" required>
                </div>
                <div class="form-actions">
                    <button type="submit">Réinitialiser le mot de passe</button>
                    <a href="#" onclick="showLoginForm()">Retour à la connexion</a>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="faq-section">
    <h2>Foire aux questions (FAQ)</h2>
    
    <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
            <h3>Qu'est-ce qu'une application "Alerte Inondation" ?</h3>
            <span>▶</span> </div>
        <div class="faq-answer">
            <p>C'est une application web cartographique conçue pour renforcer la sécurité des citoyens et accompagner les autorités dans la gestion des inondations.</p>
        </div>
    </div>
    
    <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
            <h3>Comment l'application fonctionne-t-elle ?</h3>
            <span>▶</span>
        </div>
        <div class="faq-answer">
            <p>Grâce à des données recueillies en temps réel, une alerte est envoyée aux citoyens par e-mail et SMS pour les informer 
               de la manifestation des inondations.</p>
        </div>
    </div>
    
    <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
            <h3>Où trouver les alertes?</h3>
            <span>▶</span>
        </div>
        <div class="faq-answer">
            <p>Les alertes sont diffusées via les médias locaux, les réseaux sociaux, les systèmes d'alerte communautaires et notre site web. 
              Vérifiez régulièrement les mises à jour.  </p>
        </div>
    </div>

    <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
            <h3>Comment se protéger des inondations ?</h3>
            <span>▶</span>
        </div>
        <div class="faq-answer">
            <p>Pour vous protéger des inondations, préparez un kit d'urgence, connaissez vos voies d'évacuation et suivez les instructions des autorités locales. 
              Éloignez-vous des zones inondées. </p>
        </div>
    </div>

      <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
            <h3>Quels sont les zones d'inondations ?</h3>
            <span>▶</span>
        </div>
        <div class="faq-answer">
            <p>Les zones à risque sont celles situées dans les vallées, les cuvettes.  </p>
        </div>
    </div>

    <div class="faq-item">
        <div class="faq-toggle" onclick="toggleAnswer(this)">
            <h3>Que faire en cas d'inondations ?</h3>
            <span>▶</span>
        </div>
        <div class="faq-answer">
            <p>En cas d'inondation, montez aux étages supérieurs.  
              Coupez l'électricité si possible.
               Évacuez immédiatement et contactez les secours.  </p>
        </div>
    </div>
</div>

<script>
    function showRegistrationForm() {
        document.getElementById('login-form').style.display = 'none';
        document.getElementById('forgot-password-form').style.display = 'none';
        document.getElementById('registration-form').style.display = 'block';
    }

    function showLoginForm() {
        document.getElementById('registration-form').style.display = 'none';
        document.getElementById('forgot-password-form').style.display = 'none';
        document.getElementById('login-form').style.display = 'block';
    }

    function showForgotPasswordForm() {
        document.getElementById('login-form').style.display = 'none';
        document.getElementById('registration-form').style.display = 'none';
        document.getElementById('forgot-password-form').style.display = 'block';
    }

    // Gestion de la fermeture du modal en cliquant en dehors
    window.onclick = function(event) {
        var modal = document.getElementById('login-modal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
     function toggleAnswer(element) {
        // Sélectionne le conteneur parent (faq-item)
        const faqItem = element.closest('.faq-item');
        
        // Sélectionne l'élément de réponse et l'icône de bascule
        const answer = faqItem.querySelector('.faq-answer');
        const toggle = faqItem.querySelector('.faq-toggle');

        // Bascule la classe 'show' sur la réponse
        answer.classList.toggle('show');
        
        // Bascule la classe 'active' sur le bouton pour la rotation de l'icône
        toggle.classList.toggle('active');
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