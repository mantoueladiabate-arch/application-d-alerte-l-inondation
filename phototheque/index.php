<?php
require_once __DIR__ . '/../config.php';

// Si la fonction connect n'est pas dans un fichier séparé, vous pouvez la définir ici :
function connect() {
    $conn = null;
    try {
        // Paramètres de connexion à la base de données PostgreSQL
        $conn = new PDO(DB_DSN, DB_USER, DB_PASS);
        // Configure PDO pour qu'il lance des exceptions en cas d'erreur
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $ex) {
        // En cas d'échec de connexion, enregistre l'erreur dans le log du serveur
        error_log('Echec de la connexion à la base de données : ' . $ex->getMessage());
        // Affiche un message d'erreur générique à l'utilisateur
        echo '<p style="color:red;">Erreur de connexion à la base de données. Veuillez réessayer plus tard.</p>';
        return null; // Retourne null en cas d'échec de connexion
    }
    return $conn; // Retourne l'objet de connexion PDO si réussie
}

// Établit la connexion à la base de données
$conn = connect();

$medias = []; // Tableau qui contiendra toutes les données des médias (photos/vidéos)

// Vérifie si la connexion à la base de données a été établie avec succès
if ($conn) {
    try {
        // Requête SQL pour récupérer les informations des incidents
        // Si votre colonne commune s'appelle 'nom_commune', changez 'commune' en 'nom_commune' ici.
        $stmt_medias = $conn->query("SELECT fichier, description, risques, date, commune, quartier FROM informations WHERE fichier IS NOT NULL AND fichier != '' ORDER BY date DESC");
        // Récupère tous les résultats de la requête sous forme de tableau associatif
        $medias = $stmt_medias->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // En cas d'erreur lors de l'exécution de la requête SQL, enregistre l'erreur
        error_log('Erreur de récupération des médias : ' . $e->getMessage());
        // Affiche un message d'erreur à l'utilisateur
        echo '<p style="color:red;">Erreur lors du chargement des médias de la galerie.</p>';
    }
}

// Chemin de base où les fichiers médias (photos/vidéos) sont stockés sur le serveur.
// Ce chemin est relatif à la racine de votre application web.
// TRÈS IMPORTANT : Assurez-vous que ce chemin correspond à l'endroit réel où vos fichiers sont uploadés.
// Par exemple, si votre script de galerie est dans le dossier 'www' et vos médias dans 'www/contact/informations/', ce chemin est correct.
$mediaBasePath = '../contact/informations/';

// Fonction utilitaire pour déterminer si un fichier est une image ou une vidéo basée sur son extension.
function getMediaType($filename) {
    // Extrait l'extension du nom de fichier et la convertit en minuscules
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    // Tableaux des extensions reconnues pour les images et vidéos
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    $videoExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov'];

    // Vérifie si l'extension correspond à une image
    if (in_array($extension, $imageExtensions)) {
        return 'image';
    }
    // Vérifie si l'extension correspond à une vidéo
    elseif (in_array($extension, $videoExtensions)) {
        return 'video';
    }
    // Si l'extension n'est ni image ni vidéo reconnue
    return 'unknown';
}

// Section optionnelle : Compteur des incidents de type 'Inondation'.
// C'est un exemple, vous pouvez l'adapter à d'autres types de risques.
$sql_Inondation = "SELECT count(*) AS total_inondation FROM informations WHERE risques = 'Inondation'";
$total_inondation = 0; // Initialise la variable à 0 pour éviter les erreurs si la connexion échoue ou aucun résultat
if ($conn) {
    try {
        // Exécute la requête pour compter les inondations
        $result_Inondation = $conn->query($sql_Inondation);
        // Récupère la ligne de résultat
        $row_Inondation = $result_Inondation->fetch(PDO::FETCH_ASSOC);
        // Assigne la valeur du compteur à la variable
        $total_inondation = $row_Inondation['total_inondation'];
    } catch (PDOException $e) {
        // Enregistre l'erreur si le comptage échoue
        error_log('Erreur de récupération du total d\'inondations : ' . $e->getMessage());
    }
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
     
   <style>
        /* Styles CSS personnalisés pour la galerie */
        .media-container {
            margin-top: 20px; /* Marge supérieure pour le conteneur des médias */
        }
        .card-img-top, .card-video-top {
            height: 200px; /* Hauteur fixe pour l'image ou la vidéo en haut de la carte */
            object-fit: cover; /* Recadre l'image/vidéo pour remplir la zone sans déformer, peut couper les bords */
            width: 100%; /* S'assure que le média prend toute la largeur du conteneur */
        }
        .card-body {
            /* Pas de hauteur fixe ici pour permettre au contenu de s'adapter */
        }
        .card {
            min-height: 380px; /* Hauteur minimale pour chaque carte pour l'uniformité */
            display: flex; /* Utilise flexbox pour un meilleur contrôle de la mise en page */
            flex-direction: column; /* Organise les éléments en colonne */
            justify-content: space-between; /* Espace les éléments du haut vers le bas */
        }
        .card-footer {
            padding: 0.5rem 1rem; /* Padding pour le pied de carte */
            background-color: rgba(0,0,0,.03); /* Couleur de fond légère */
            border-top: 1px solid rgba(0,0,0,.125); /* Bordure supérieure */
            font-size: 0.85em; /* Taille de police légèrement plus petite */
            color: #6c757d; /* Couleur de texte grisée */
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
          
        </nav>
      </header>
    </div>
    
<div class="container">
    <h2>Galerie des Incidents Signalés</h2>

    <?php if ($total_inondation > 0): ?>
        <p class="text-center text-muted">Nombre total de risques naturels de type signalés : <?php echo $total_inondation; ?></p>
    <?php endif; ?>

    <div class="row media-container">
        <?php if (empty($medias)): ?>
            <p class="text-center">Aucun média (photo ou vidéo) disponible dans la galerie pour le moment.</p>
        <?php else: ?>
            <?php foreach ($medias as $media):
                // Construit le chemin complet du fichier média
                $fullPath = $mediaBasePath . htmlspecialchars($media['fichier']);
                // Détermine si le média est une image, une vidéo ou inconnu
                $mediaType = getMediaType($media['fichier']);
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if ($mediaType === 'image'): ?>
                            <img src="<?php echo $fullPath; ?>" class="card-img-top" alt="Image de l'incident">
                        <?php elseif ($mediaType === 'video'): ?>
                            <video class="card-video-top" controls>
                                <source src="<?php echo $fullPath; ?>" type="video/<?php echo strtolower(pathinfo($media['fichier'], PATHINFO_EXTENSION)); ?>">
                                Votre navigateur ne supporte pas la balise vidéo.
                            </video>
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light text-muted" style="height: 200px;">
                                Fichier non supporté
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($media['risques'] ?? 'Incident'); ?></h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars(substr($media['description'] ?? 'Pas de description.', 0, 150) . (strlen($media['description'] ?? '') > 150 ? '...' : ''))); ?></p>

                            <a href="<?php echo $fullPath; ?>" download class="btn btn-success btn-sm mt-2">Télécharger</a>
                        </div>

                        <div class="card-footer">
                            <?php
                            // Formatte la date si elle existe et est valide
                            $date_formatted = 'Date inconnue';
                            if (isset($media['date']) && !empty($media['date'])) {
                                try {
                                    $datetime_obj = new DateTime($media['date']);
                                    $date_formatted = $datetime_obj->format('d/m/Y H:i');
                                } catch (Exception $e) {
                                    // Log l'erreur si le format de date est invalide (pour le débogage côté serveur)
                                    error_log('Erreur de format de date pour le média : ' . ($media['fichier'] ?? 'N/A') . ' - ' . $e->getMessage());
                                    $date_formatted = 'Date invalide'; // Affiché à l'utilisateur
                                }
                            }

                            // Récupère le nom de la commune, affichant 'Non spécifiée' si la valeur est null ou non définie
                            $commune_name = htmlspecialchars($media['commune'] ?? 'Non spécifiée');
                            // Récupère le nom du quartier, affichant 'Non spécifié' si la valeur est null ou non définie
                            $quartier_name = htmlspecialchars($media['quartier'] ?? 'Non spécifié');
                            ?>
                            <small class="text-muted">
                                Signalé le: <?php echo $date_formatted; ?><br>
                                Commune: <?php echo $commune_name; ?><br>
                                Quartier: <?php echo $quartier_name; ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
 
    <script>
      $.widget.bridge('uibutton', $.ui.button);
    </script>
 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
   
    
        
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>