<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// connection à la base données
  function connect() {
    $conn = null;

    try{
      // On se connecte à la base de donnée PostgreSQL
       $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','postgres'); // On se connecte à la base de donnée
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } 
    // S'il existe un problème de connection, on obtient le message d'erreur
    catch(PDOException $ex) {
      error_log('Connection error : ' . $ex->getMessage());
     // echo 'Connection error : ' . $ex->getMessage(); 
     // Ne pas echo d'erreur de connexion HTML ici si nous attendons du JSON
        return null;
    }
    return $conn;
  }
  
// --- Vérifier si la requête est bien une requête POST ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // ÉTABLIR LA CONNEXION PDO (CORRECTION CRITIQUE)
    $pdo = connect(); 

    if (!$pdo) {
        // La connexion a échoué.
        http_response_code(500); // Définir un statut d'erreur HTTP
        echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données. Veuillez vérifier les paramètres.']);
        exit();
    }

    // --- Récupérer les données du formulaire ---
    // Les données sont déjà nettoyées via les requêtes préparées (PDO)
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $contact1 = $_POST['contact1'] ?? '';
    $whatsapp = $_POST['whatsapp'] ?? null; // Utilisez null pour les champs vides facultatifs
    $mail = $_POST['mail'] ?? null;         // Utilisez null pour les champs vides facultatifs
    
    // Valider les champs requis côté serveur
    if (empty($nom) || empty($prenom) || empty($contact1)) {
      http_response_code(400); // Mauvaise requête
        echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires (Nom, Prénom, Contact 1).']);
        exit();
    }
    // --- Préparer la requête SQL d'insertion avec des paramètres nommés ou positionnels ---

    $sql = "INSERT INTO contacts (nom, prenom, contact1, whatsapp, mail) 
            VALUES (:nom, :prenom, :contact1, :whatsapp, :mail)";
            
    try {
        $stmt = $pdo->prepare($sql);

        // Lier les valeurs aux paramètres de la requête préparée
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':contact1', $contact1);
        $stmt->bindParam(':whatsapp', $whatsapp);
        $stmt->bindParam(':mail', $mail);

        // Exécuter la requête
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Contact enregistré avec succès !']);
        } else {
            // PDOException devrait déjà capturer les erreurs d'exécution, mais c'est une sécurité
            error_log("Erreur d'exécution de la requête : " . json_encode($stmt->errorInfo()));
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du contact.']);
        }

    } catch (PDOException $e) {
        // Capturer les exceptions spécifiques à l'exécution de la requête
        error_log("Erreur de requête préparée/exécution : " . $e->getMessage());
         http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur interne du serveur lors de l\'opération de base de données.']);
    }
 exit(); // Terminer le script après avoir envoyé la réponse JSON

} 
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Formulaire d'Enregistrement de Contact</title>
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
     /*largeur et centrer du texte*/
      form {
          padding:30px 60px;     
		  }
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        h2 {
            color: #343a40;
            margin-bottom: 25px;
        }
        .form-group label {
            font-weight: 600;
            color: #495057;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        #responseMessage {
            margin-top: 20px;
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
         <span class="txt">Enregistrer un Nouveau Contact</span> 
          
        </nav>
      </header>
    </div>
    

    <div class="form-container">
        <!--h2 class="text-center">Enregistrer un Nouveau Contact</h2-->
        <form id="contactForm">
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom :</label>
                <input type="text" class="form-control" id="prenom" name="prenom" required>
            </div>
            <div class="form-group">
                <label for="contact1">Contact 1 :</label>
                <input type="tel" class="form-control" id="contact1" name="contact1" pattern="[0-9]{8,15}" title="Veuillez entrer un numéro de téléphone valide (8 à 15 chiffres)" required>
                <small class="form-text text-muted">Ex: 07XXXXXXXX ou +225XXXXXXXX</small>
            </div>
            <div class="form-group">
                <label for="whatsapp">Numéro WhatsApp :</label>
                <input type="tel" class="form-control" id="whatsapp" name="whatsapp" pattern="[0-9]{8,15}" title="Veuillez entrer un numéro de téléphone valide (8 à 15 chiffres)">
                <small class="form-text text-muted">Optionnel</small>
            </div>
            <div class="form-group">
                <label for="mail">Email :</label>
                <input type="email" class="form-control" id="mail" name="mail">
                <small class="form-text text-muted">Optionnel</small>
            </div>
            <button type="submit" class="btn btn-primary">Valider</button>
        </form>

        <div id="responseMessage" class="alert mt-3" style="display: none;"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Empêche l'envoi traditionnel du formulaire

            const form = event.target;
            const formData = new FormData(form); // Récupère toutes les données du formulaire
            const responseMessageDiv = document.getElementById('responseMessage');

            // Afficher le message de chargement
            responseMessageDiv.style.display = 'block';
            responseMessageDiv.className = 'alert alert-info';
            responseMessageDiv.textContent = 'Envoi des données en cours...';

             const apiUrl = document.location.href; 
           // fetch('process_form.php', {
              fetch(apiUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Parse la réponse JSON du serveur
            .then(data => {
                if (data.success) {
                    responseMessageDiv.className = 'alert alert-success';
                    responseMessageDiv.textContent = 'Contact enregistré avec succès !';
                    form.reset(); // Réinitialise le formulaire après succès
                } else {
                    responseMessageDiv.className = 'alert alert-danger';
                    responseMessageDiv.textContent = 'Erreur lors de l\'enregistrement : ' + (data.message || 'Erreur inconnue.');
                }
                // Cacher le message après quelques secondes
                setTimeout(() => {
                    responseMessageDiv.style.display = 'none';
                }, 3000);
            })
            .catch(error => {
               // console.error('Erreur réseau ou du serveur:', error);
                responseMessageDiv.className = 'alert alert-danger';
                responseMessageDiv.textContent = 'Erreur de communication avec le serveur.';
                setTimeout(() => {
                    responseMessageDiv.style.display = 'none';
                }, 3000);
            });
        });
    </script>
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script>  $.widget.bridge('uibutton', $.ui.button); </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>