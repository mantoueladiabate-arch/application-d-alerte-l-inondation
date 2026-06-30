<?php
error_reporting(E_ALL); // Affiche toutes les erreurs, avertissements, etc.
ini_set('display_errors', 1); // Force l'affichage à l'écran

function connect() {
    $conn = null;

    try{
         // On se connecte à MySQL
      $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','postgres');
    // Définit le mode d'erreur de PDO sur les exceptions pour une gestion robuste des erreurs
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Définit le mode de récupération par défaut sur les tableaux associatifs
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } 
    // S'il existe un problème de connection, on obtient le message d'erreur
    catch(PDOException $ex) {
      // Enregistre l'erreur pour le débogage côté serveur, ne pas afficher l'erreur brute à l'utilisateur
        error_log('Erreur de connexion : ' . $ex->getMessage());
        // Envoie une réponse d'erreur 500 Internal Server Error
        http_response_code(500);
        // Assurez-vous que l'en-tête et le nettoyage sont faits AVANT d'envoyer l'erreur JSON
        header('Content-Type: application/json');
        ob_clean(); 
        echo json_encode(['error' => 'La connexion à la base de données a échoué. Veuillez réessayer plus tard.']);
        exit(); // Crucial : Arrête l'exécution du script si la connexion échoue
      
    }
    return $conn;
  }

if(isset($_GET['elemid'])) {

    $elemeid = $_GET['elemid'];

    $conn = connect();  // Tente de se connecter à la base de données

    try {

    switch($elemeid) {
// affichier les nombres de chaques risques lors de la selection d'une commune
      case 'information_commune':


       // Valide et nettoie l'entrée pour prévenir l'injection SQL
                $id_commune = filter_var($_GET['id_commune'], FILTER_VALIDATE_INT);

                if ($id_commune === false) {
                    http_response_code(400); // Requête incorrecte
                     header('Content-Type: application/json'); // Assurez-vous que l'en-tête est là
                    ob_clean();
                    echo json_encode(['error' => 'ID de commune invalide.']);
                    exit();
                }

                // Utilise une requête préparée pour prévenir l'injection SQL
      
        $sql = 'select 
        
 COUNT(CASE WHEN risques = \'Inondation\' THEN 1 END) AS tt_inondation,
 COUNT(CASE WHEN risques = \'Erosion\' THEN 1 END) AS tt_erosion,
 COUNT(CASE WHEN risques = \'Eboulement\' THEN 1 END) AS tt_eboulement,
COUNT(CASE WHEN risques = \'Effondrement\' THEN 1 END) AS tt_effondrement,
COUNT(*) AS tt_risques
FROM informations 
WHERE commune = :id_commune'; // Utilise un placeholder where commune = ' . $id_commune;


        

$stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_commune', $id_commune, PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch(); // Récupère la seule ligne de résultat

                // Affiche la sortie en JSON pour une gestion robuste des données
                header('Content-Type: application/json');
                ob_clean();
                echo json_encode([
                    'tt_inondation' => (int)$row['tt_inondation'], // Convertit en entier pour assurer une sortie numérique
                    'tt_erosion'    => (int)$row['tt_erosion'],
                    'tt_eboulement' => (int)$row['tt_eboulement'],
                    'tt_effondrement' => (int)$row['tt_effondrement'],
                    'tt_risques' => (int)$row['tt_risques']
                ], JSON_NUMERIC_CHECK); // S'assure que les nombres ne sont pas retournés comme des chaînes


        break;

    



        case 'zoom_commune':
                // Valide et nettoie l'entrée pour prévenir l'injection SQL
                $id_commune = filter_var($_GET['id_commune'], FILTER_VALIDATE_INT);

                if ($id_commune === false) {
                    http_response_code(400); // Requête incorrecte
                    // Ajoutez les en-têtes et le nettoyage ici
                    header('Content-Type: application/json');
                    ob_clean();
                    echo json_encode(['error' => 'ID de commune invalide.']);
                    exit();
                }

                // Utilise une requête préparée pour prévenir l'injection SQL
                // SQL corrigé : suppression du "id" en trop, SRID et transformation supposés corrects pour vos données.
                // Si la géométrie est déjà en 4326, utilisez ST_AsGeoJSON(geom, 6)
                // Si la géométrie est en 3857 et que vous voulez la transformer en 4326, utilisez ST_AsGeoJSON(ST_Transform(geom, 4326), 6)
                $sql = 'SELECT id, public.ST_AsGeoJSON(ST_Transform(geom, 4326)) as geom, commune as nom_commune
            FROM public.communes_abidjan
            WHERE id = :id_commune'; // Utilise un placeholder

                $stmt = $conn->prepare($sql);
               $stmt->bindParam(':id_commune', $id_commune, PDO::PARAM_INT);
               $stmt->execute();

     $geojson = [
        'type' => 'FeatureCollection',
        'features' => []
    ];

    while($row = $stmt->fetch()){
    $feature = [
        'type' => 'Feature',
        'geometry' => json_decode($row['geom'], true),
        'properties' => [ // Créez directement un tableau avec les propriétés que vous voulez
            'id' => $row['id'], // Ajoutez explicitement l'ID si vous le voulez aussi
            'nom_commune' => $row['nom_commune'] // C'est cette ligne qui est cruciale
        ]
    ];
    array_push($geojson['features'], $feature);
}
                  // C'est l'endroit le plus important pour ajouter les en-têtes et le nettoyage
                header('Content-Type: application/json');
                ob_clean();
                echo json_encode($geojson, JSON_NUMERIC_CHECK);
                break;

            default:
                // Gère gracieusement les elemeid non supportés
                http_response_code(400); // Requête incorrecte
                // Ajoutez les en-têtes et le nettoyage ici
                header('Content-Type: application/json');
                ob_clean();
                echo json_encode(['error' => 'ID d\'élément non supporté.']);
                exit();
        }
    } catch (PDOException $e) {
        // Capture toutes les erreurs liées à la base de données pendant l'exécution de la requête
        error_log('Erreur de base de données : ' . $e->getMessage());
        http_response_code(500); // Erreur interne du serveur
        // Ajoutez les en-têtes et le nettoyage ici
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode(['error' => 'Une erreur de base de données est survenue.']);
    } finally {
        // Ferme le curseur si ouvert (important pour la gestion des ressources, bien que la fin du script PHP nettoie)
        if (isset($stmt) && $stmt instanceof PDOStatement) {
            $stmt->closeCursor();
        }
    }

} else {
    // Si le paramètre 'elemid' est manquant
    http_response_code(400); // Requête incorrecte
    // Ajoutez les en-têtes et le nettoyage ici
        header('Content-Type: application/json');
        ob_clean();
    echo json_encode(['error' => 'Paramètre elemid manquant.']);
}




?>