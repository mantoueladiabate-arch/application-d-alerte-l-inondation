<?php
session_start();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: index.php?error=champs_vides');
    exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Détection automatique du nom de colonne selon la machine
    $colCheck = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='utilisateurs' AND column_name IN ('user_name','username') LIMIT 1");
    $colRow   = $colCheck->fetch(PDO::FETCH_ASSOC);
    $userCol  = $colRow ? $colRow['column_name'] : 'user_name';

    $stmt = $pdo->prepare("SELECT id, nom, prenom, $userCol AS user_name, role, mot_de_passe FROM utilisateurs WHERE $userCol = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['user_name'];
        $_SESSION['nom']      = $user['nom'];
        $_SESSION['prenom']   = $user['prenom'];
        $_SESSION['role']     = $user['role'];

        // Redirection selon le role
        if (in_array($user['role'], ['admin', 'administrateur'])) {
            header('Location: utilisateurs/index.php');
        } else {
            header('Location: analyste/index.php');
        }
        exit;
    } else {
        header('Location: index.php?error=identifiants_incorrects');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php?error=erreur_serveur');
    exit;
}
