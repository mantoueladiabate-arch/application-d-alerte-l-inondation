<?php
// Fonction de connexion à la base de données (la même que dans index.php)
function connect() {
    $conn = null;
    try {
        $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation', 'postgres', 'postgres');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $ex) {
        error_log('Échec de la connexion à la base de données dans ajax.php: ' . $ex->getMessage());
        return null;
    }
    return $conn;
}

if (!isset($_GET['elemid'])) {
    echo json_encode(['success' => false, 'error' => 'No elemid specified.']);
    exit();
}

$conn = connect();
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit();
}

header('Content-Type: application/json');

switch ($_GET['elemid']) {
    case 'get_communes_quartiers':
        $communes_et_quartiers = [];
        try {
            // Récupère les communes pour le menu déroulant
            $sql_communes = 'SELECT "commune" FROM public.communes_abidjan ORDER BY "commune"';
            $result_communes = $conn->query($sql_communes);
            $communes = $result_communes->fetchAll(PDO::FETCH_COLUMN);

            // Récupère la liste des quartiers par commune pour le JS
            $sql_quartiers = 'SELECT "commune", nom_quart FROM public.quartiers_abidjan ORDER BY "commune", nom_quart';
            $result_quartiers = $conn->query($sql_quartiers);
            while ($row = $result_quartiers->fetch(PDO::FETCH_ASSOC)) {
                if (!isset($communes_et_quartiers[$row['commune']])) {
                    $communes_et_quartiers[$row['commune']] = [];
                }
                if (!empty($row['nom_quart'])) {
                    $communes_et_quartiers[$row['commune']][] = $row['nom_quart'];
                }
            }
            echo json_encode(['success' => true, 'communes' => $communes, 'communes_et_quartiers' => $communes_et_quartiers]);
        } catch (PDOException $ex) {
            echo json_encode(['success' => false, 'error' => 'SQL Error: ' . $ex->getMessage()]);
        }
        break;

    case 'get_risks_by_quartier':
        $selectedCommune = $_GET['commune'] ?? 'Abidjan';
        $selectedQuartier = $_GET['quartier'] ?? 'Tous';
        $selectedRisque = $_GET['risque'] ?? 'Total';

        $query_quartiers = '';
        if ($selectedCommune !== 'Abidjan' && $selectedQuartier !== 'Tous') {
   
        }

        $risques_par_quartier = [];
        $max_risques = 0;
        try {
            $sql = "
                SELECT
                    quartier,
                    risques,
                    COUNT(*) as total_risques
                FROM
                    public.informations
                WHERE
                    quartier IS NOT NULL AND quartier != '' AND new_statut = 'traite'
                    $query_quartiers
                GROUP BY
                    quartier, risques
            ";
            $result = $conn->query($sql);

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $quartier = $row['quartier'];
                $type_risque = $row['risques'];
                $count = (int)$row['total_risques'];

                if (!isset($risques_par_quartier[$quartier])) {
                    $risques_par_quartier[$quartier] = ['total' => 0, 'par_type' => []];
                }
                $risques_par_quartier[$quartier]['par_type'][$type_risque] = $count;
                $risques_par_quartier[$quartier]['total'] += $count;
            }

            // Calcul du max_risques pour la légende
            foreach ($risques_par_quartier as $data) {
                $current_count = ($selectedRisque === 'Total') ? $data['total'] : ($data['par_type'][$selectedRisque] ?? 0);
                if ($current_count > $max_risques) {
                    $max_risques = $current_count;
                }
            }

            echo json_encode(['success' => true, 'data' => $risques_par_quartier, 'max_risques' => $max_risques]);
        } catch (PDOException $ex) {
            echo json_encode(['success' => false, 'error' => 'SQL Error: ' . $ex->getMessage()]);
        }
        break;z

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid elemid.']);
        break;
}
?>