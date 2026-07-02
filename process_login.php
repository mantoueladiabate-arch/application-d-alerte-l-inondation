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

    $stmt = $pdo->prepare("SELECT id, nom, prenom, user_name, role, mot_de_passe FROM utilisateurs WHERE user_name = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['user_name'];
        $_SESSION['nom']      = $user['nom'];
        $_SESSION['prenom']   = $user['prenom'];
        $_SESSION['role']     = $user['role'];

        // Redirection selon le role
        if ($user['role'] === 'admin' || $user['role'] === 'administrateur') {
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
