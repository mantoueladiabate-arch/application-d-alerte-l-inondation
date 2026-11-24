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

$id = $_GET['id'] ?? null;

$colonnes = "
    id_bilan, id_alerte, redacteur_nom, redacteur_fonction, organisme,
    statut_bilan, date_creation, date_maj, date_debut, date_fin,
    commune, quartier, repere_precis, type_milieu, latitude, longitude,
    causes_meteo, causes_urbanisme_drainage, details_causes,
    nb_deces, nb_blesses, nb_disparus, nb_menages_sinistres, nb_personnes_deplacees,
    risques_sanitaires, infrastructures_endommagees,
    nb_habitations_touchees, nb_habitations_detruites, activites_touchees, estimation_pertes,
    actions_urgence, acteurs_mobilises, moyens_materiels, difficultes_intervention,
    actions_post_crise, avancement_post_crise,
    mesures_structurelles, mesures_non_structurelles, action_prioritaire, responsable_action, horizon_action
";

try {
    if ($id) {
        $stmt = $pdo->prepare("SELECT $colonnes FROM bilans WHERE id_bilan = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Bilan non trouvé.']);
        }
    } else {
        $stmt = $pdo->query("SELECT $colonnes FROM bilans ORDER BY id_bilan DESC");
        $rows = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $rows]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
