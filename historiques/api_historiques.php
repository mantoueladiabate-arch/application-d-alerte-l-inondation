<?php
// Définit l'en-tête Content-Type pour indiquer que la réponse sera au format JSON.
header('Content-Type: application/json');

header('Access-Control-Allow-Origin: *');
// Spécifie les méthodes HTTP autorisées pour cette ressource.
header('Access-Control-Allow-Methods: GET, OPTIONS');
// Spécifie les en-têtes qui peuvent être utilisés lors des requêtes pré-vol CORS.
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// --- Configuration de la base de données PostgreSQL ---
// Définition des constantes pour la connexion à la base de données.
define('DB_HOST', 'localhost'); // L'adresse de l'hôte de la base de données.
define('DB_PORT', '5432');     // Le port par défaut de PostgreSQL.
define('DB_NAME', 'base_inondation'); // Le nom de votre base de données.
define('DB_USER', 'postgres'); // Le nom d'utilisateur pour la connexion à la base de données.
define('DB_PASS', 'postgres'); // <<< VOTRE MOT DE PASSE POUR POSTGRES (À REMPLACER !). Assurez-vous que c'est le bon mot de passe.

// --- Connexion à la base de données avec PDO (PHP Data Objects) ---
try {
    // Construction de la chaîne DSN (Data Source Name) pour la connexion PostgreSQL.
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    // Crée une nouvelle instance PDO pour établir la connexion.
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    // Configure PDO pour lancer des exceptions en cas d'erreur SQL, ce qui facilite le débogage.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Définit le mode de récupération par défaut des résultats des requêtes en tableau associatif.
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // En cas d'échec de connexion, enregistre l'erreur et renvoie une réponse JSON d'échec.
    error_log('Échec de la connexion à la base de données : ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Échec de la connexion à la base de données.']);
    exit(); // Arrête l'exécution du script.
}

// Récupère la méthode de la requête HTTP (GET, POST, PUT, DELETE, etc.).
$method = $_SERVER['REQUEST_METHOD'];


switch ($method) {
    case 'GET':
        // Tente de récupérer un ID d'incident si celui-ci est fourni dans l'URL (ex: api_historiques.php?id=123).
        $id = $_GET['id'] ?? null;

        if ($id) {
            // --- Logique pour récupérer un incident TRAITÉ spécifique par son ID ---
            try {
                $stmt = $pdo->prepare("SELECT id, commune, quartier, risques, description, fichier, date_signalisation, latitude, longitude, date, new_statut, recommandation, last_updated FROM informations WHERE id = :id AND new_statut = 'traite'");
                // Lier le paramètre :id à la valeur de la variable $id, en spécifiant que c'est un entier.
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                // Exécute la requête préparée.
                $stmt->execute();
                // Récupère la première (et unique) ligne de résultat.
                $incident = $stmt->fetch();

                if ($incident) {
                    // Si un incident correspondant et traité est trouvé, le renvoie en JSON.
                    echo json_encode(['success' => true, 'data' => $incident]);
                } else {
                    // Si aucun incident correspondant ou traité n'est trouvé.
                    echo json_encode(['success' => false, 'message' => 'Incident traité non trouvé ou non accessible.']);
                }
            } catch (PDOException $e) {
                // En cas d'erreur lors de l'exécution de la requête SQL, renvoie un message d'erreur JSON.

                
                error_log("Erreur lors de la récupération de l'incident traité par ID: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération de l\'incident traité : ' . $e->getMessage()]);
            }
        } else {
            // --- Logique pour récupérer TOUS les incidents TRAITÉS ---
            try {
               
                $stmt = $pdo->query("SELECT id, commune, quartier, risques, description, fichier, date_signalisation, latitude, longitude, date, new_statut, recommandation, last_updated FROM informations WHERE new_statut = 'traite' ORDER BY date DESC");
                // Récupère toutes les lignes de résultat.
                $incidents = $stmt->fetchAll();
                // Renvoie la liste des incidents traités en JSON.
                echo json_encode(['success' => true, 'data' => $incidents]);
            } catch (PDOException $e) {
                // En cas d'erreur lors de l'exécution de la requête SQL, renvoie un message d'erreur JSON.
                error_log("Erreur lors de la récupération de tous les incidents traités: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des incidents traités : ' . $e->getMessage()]);
            }
        }
        break;

    default:
        // Si la méthode HTTP de la requête n'est pas supportée par cette API, renvoie un message d'erreur.
        echo json_encode(['success' => false, 'message' => 'Méthode de requête non supportée. Seules les requêtes GET sont acceptées pour l\'historique.']);
        break;
}
?>