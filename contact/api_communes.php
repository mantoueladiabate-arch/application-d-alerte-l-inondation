<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Api-Key');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config.php';

$headerKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!hash_equals(MOBILE_API_KEY, $headerKey)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit();
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $stmt = $pdo->query('SELECT id, commune FROM communes_abidjan ORDER BY commune');
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Échec de la connexion.']);
}
