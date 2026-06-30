<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Autorise les requêtes de n'importe quelle origine (pour le développement)
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Gérer les requêtes OPTIONS (pré-vol CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// --- Configuration de la base de données PostgreSQL ---
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'base_inondation'); // Assurez-vous que c'est votre BDD utilisateurs
define('DB_USER', 'postgres');
define('DB_PASS', 'postgres'); // <<< REMPLACER CECI

// --- Connexion à la base de données avec PDO ---
try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Échec de la connexion à la base de données : ' . $e->getMessage()]);
    exit();
}

// --- Configuration des identifiants API ---
define('CLIENT_ID', 'OVLSERVICES_GtC9346');
define('CLIENT_SECRET', 'OVLSERVICES20251024094434.7668209a8NIMFxWldCgbbmFPCZ');

// Récupération des numéros
$result = pg_query($conn, "SELECT numero FROM utilisateurs WHERE recevoir_alertes = TRUE");

if (!$result) {
    die("Erreur lors de la récupération des numéros");
}

// Ton token d’authentification API
$api_token = "TON_TOKEN_ICI";

// Boucle sur les destinataires
while ($row = pg_fetch_assoc($result)) {
    $numero = $row['numero'];

    // Préparation du message
    $data = [
        "to"   => $numero,
        "body" => "Alerte inondation : Forte pluie et risque d’inondation dans la zone d’Allabra.
       Évite les routes inondées. Mets-toi en sécurité.
       Écoute les consignes des autorités."
    ];

    // Requête HTTP POST vers l’API SMS
    $ch = curl_init("https://api.smsprovider.com/send"); // Remplace par l’URL de ton fournisseur
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "Erreur pour $numero : " . curl_error($ch) . "\n";
    } else {
        echo "Message envoyé à $numero : $response\n";
    }

    curl_close($ch);
}

pg_close($conn);
?>


