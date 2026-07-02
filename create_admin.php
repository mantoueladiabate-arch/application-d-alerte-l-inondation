<?php
require_once __DIR__ . '/config.php';

$nom      = 'mantouela';
$prenom   = 'diabate';
$user_name = 'admin';
$contact  = '0700000000';
$mail     = 'mantoueladiabate@outlook.com';
$mdp      = '12345678';
$role     = 'admin';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si l'user_name ou mail existe déjà
    $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE user_name = :u OR mail = :m");
    $check->execute([':u' => $user_name, ':m' => $mail]);
    if ($check->fetch()) {
        die("Un utilisateur avec ce user_name ou cet email existe déjà.");
    }

    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (nom, prenom, user_name, contact1, mail, mot_de_passe, role)
        VALUES (:nom, :prenom, :user_name, :contact1, :mail, :mdp, :role)
    ");
    $stmt->execute([
        ':nom'      => $nom,
        ':prenom'   => $prenom,
        ':user_name' => $user_name,
        ':contact1' => $contact,
        ':mail'     => $mail,
        ':mdp'      => password_hash($mdp, PASSWORD_DEFAULT),
        ':role'     => $role,
    ]);

    echo "<b style='color:green'>Admin créé avec succès !</b><br>";
    echo "Username : <b>$user_name</b><br>";
    echo "Mot de passe : <b>$mdp</b><br>";
    echo "<br><span style='color:red'>⚠ Supprime ce fichier immédiatement après !</span>";

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
