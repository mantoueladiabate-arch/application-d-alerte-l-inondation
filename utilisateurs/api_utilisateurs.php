<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Autorise les requêtes de n'importe quelle origine (pour le développement)
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Gérer les requêtes OPTIONS (pré-vol CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config.php';

// --- Connexion à la base de données avec PDO ---
try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Échec de la connexion à la base de données : ' . $e->getMessage()]);
    exit();
}

// Récupérer la méthode de la requête HTTP
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true); // Pour PUT/DELETE/POST avec JSON

switch ($method) {
    case 'GET':
        // Récupérer un utilisateur spécifique ou tous les utilisateurs
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("SELECT id, nom, prenom, username, contact1, mail, role, date_creation FROM utilisateurs WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch();
            if ($user) {
                echo json_encode(['success' => true, 'data' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
            }
        } else {
            $stmt = $pdo->query("SELECT id, nom, prenom, username, contact1, mail, role, date_creation FROM utilisateurs ORDER BY id DESC");
            $users = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $users]);
        }
        break;

    case 'POST': // Créer un nouvel utilisateur
        $nom = $input['nom'] ?? '';
        $prenom = $input['prenom'] ?? '';
        $username = $input['username'] ?? '';
        $contact1 = $input['contact1'] ?? '';
        $mail = $input['mail'] ?? '';
        $mot_de_passe = $input['mot_de_passe'] ?? '';
        $confirm_mot_de_passe = $input['confirm_mot_de_passe'] ?? '';
        $role = $input['role'] ?? 'analyste'; // Par défaut 'analyste'

        if (empty($nom) || empty($prenom) || empty($username) || empty($contact1) || empty($mail) || empty($mot_de_passe) || empty($confirm_mot_de_passe)) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis.']);
            exit();
        }
        if ($mot_de_passe !== $confirm_mot_de_passe) {
            echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
            exit();
        }
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Format d\'email invalide.']);
            exit();
        }

        // Vérifier si l'email existe déjà
        $stmt_check_mail = $pdo->prepare("SELECT id FROM utilisateurs WHERE mail = :mail");
        $stmt_check_mail->bindParam(':mail', $mail);
        $stmt_check_mail->execute();
        if ($stmt_check_mail->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.']);
            exit();
        }

        $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        $sql = "INSERT INTO utilisateurs (nom, prenom, username, contact1, mail, mot_de_passe, role) VALUES (:nom, :prenom, :username, :contact1, :mail, :mot_de_passe, :role)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':contact1', $contact1);
        $stmt->bindParam(':mail', $mail);
        $stmt->bindParam(':mot_de_passe', $hashed_password);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Compte utilisateur créé avec succès !']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du compte.']);
        }
        break;

    case 'PUT': // Modifier un utilisateur
        $id = $_GET['id'] ?? null; // L'ID est généralement passé dans l'URL pour PUT/DELETE
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant pour la modification.']);
            exit();
        }

        $nom = $input['nom'] ?? '';
        $prenom = $input['prenom'] ?? '';
        $username = $input['username'] ?? '';
        $contact1 = $input['contact1'] ?? '';
        $mail = $input['mail'] ?? '';
        $mot_de_passe = $input['mot_de_passe'] ?? ''; // Optionnel, si vide ne pas changer
        $confirm_mot_de_passe = $input['confirm_mot_de_passe'] ?? '';
        $role = $input['role'] ?? '';

        if (empty($nom) || empty($prenom) || empty($username) || empty($contact1) || empty($mail) || empty($role)) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires (sauf mot de passe) doivent être remplis.']);
            exit();
        }
        if ($mot_de_passe && $mot_de_passe !== $confirm_mot_de_passe) {
            echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
            exit();
        }
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Format d\'email invalide.']);
            exit();
        }

        // Vérifier si le nouvel email existe déjà pour un AUTRE utilisateur
        $stmt_check_mail = $pdo->prepare("SELECT id FROM utilisateurs WHERE mail = :mail AND id != :id");
        $stmt_check_mail->bindParam(':mail', $mail);
        $stmt_check_mail->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_check_mail->execute();
        if ($stmt_check_mail->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé par un autre utilisateur.']);
            exit();
        }

        $sql = "UPDATE utilisateurs SET nom = :nom, prenom = :prenom, username = :username, contact1 = :contact1, mail = :mail, role = :role";
        if (!empty($mot_de_passe)) {
            $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $sql .= ", mot_de_passe = :mot_de_passe";
        }
        $sql .= " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':contact1', $contact1);
        $stmt->bindParam(':mail', $mail);
        $stmt->bindParam(':role', $role);
        if (!empty($mot_de_passe)) {
            $stmt->bindParam(':mot_de_passe', $hashed_password);
        }
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Informations utilisateur mises à jour avec succès !']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour des informations.']);
        }
        break;

    case 'DELETE': // Supprimer un utilisateur
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant pour la suppression.']);
            exit();
        }

        $sql = "DELETE FROM utilisateurs WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de l\'utilisateur.']);
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Méthode de requête non supportée.']);
        break;
}
?>