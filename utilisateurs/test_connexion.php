<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    echo "Connexion à la base de données réussie !";
} catch (PDOException $e) {
    echo "Échec de la connexion : " . $e->getMessage();
}
?>