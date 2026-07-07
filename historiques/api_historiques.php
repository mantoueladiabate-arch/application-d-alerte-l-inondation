<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Échec de la connexion.']);
    exit();
}

function resolveCommune($pdo, $commune) {
    if (is_numeric($commune)) {
        $s = $pdo->prepare('SELECT commune FROM communes_abidjan WHERE id = :id');
        $s->execute([':id' => (int)$commune]);
        $r = $s->fetch();
        return $r ? $r['commune'] : $commune;
    }
    return $commune;
}

$id = $_GET['id'] ?? null;

try {
    if ($id) {
        $stmt = $pdo->prepare("
            SELECT id, commune, quartier, risques, description, fichier,
                   latitude, longitude, date, new_statut, recommandation
            FROM informations
            WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row) {
            $row['commune'] = resolveCommune($pdo, $row['commune']);
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incident non trouvé.']);
        }
    } else {
        $stmt = $pdo->query("
            SELECT id, commune, quartier, risques, description, fichier,
                   latitude, longitude, date, new_statut, recommandation
            FROM informations
            ORDER BY date DESC");
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['commune'] = resolveCommune($pdo, $row['commune']);
        }
        echo json_encode(['success' => true, 'data' => $rows]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
