<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$lat = $_GET['lat'] ?? null;
$lon = $_GET['lon'] ?? null;

if (!isset($lat, $lon) || !is_numeric($lat) || !is_numeric($lon)) {
    echo json_encode(['success' => false, 'message' => 'Coordonnées invalides']);
    exit;
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // communes_abidjan est en SRID 3857, quartier est en SRID 4326
    $point3857 = 'ST_Transform(ST_SetSRID(ST_MakePoint(:lon, :lat), 4326), 3857)';
    $point4326 = 'ST_SetSRID(ST_MakePoint(:lon2, :lat2), 4326)';

    $stmt = $pdo->prepare("
        SELECT id, commune
        FROM communes_abidjan
        WHERE ST_Contains(geom, $point3857)
        LIMIT 1
    ");
    $stmt->execute([':lat' => (float)$lat, ':lon' => (float)$lon]);
    $commune = $stmt->fetch(PDO::FETCH_ASSOC);

    $result = ['success' => true, 'commune' => null, 'quartier' => null];

    if ($commune) {
        $result['commune'] = ['id' => $commune['id'], 'nom' => $commune['commune']];

        $stmt2 = $pdo->prepare("
            SELECT nom_quartier
            FROM quartier
            WHERE ST_Contains(geom, $point4326)
            LIMIT 1
        ");
        $stmt2->execute([':lat2' => (float)$lat, ':lon2' => (float)$lon]);
        $quartier = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($quartier) {
            $result['quartier'] = $quartier['nom_quartier'];
        }
    }

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
