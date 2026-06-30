<?php
error_reporting(E_ALL); // Affiche toutes les erreurs, avertissements, etc.
ini_set('display_errors', 1); // Force l'affichage à l'écran

function connect() {
    $conn = null;

    try{
        // On se connecte à MySQL
        $conn = new PDO('pgsql:host=localhost;port=5432;dbname=base_inondation','postgres','postgres');
        // Définit le mode d'erreur de PDO sur les exceptions pour une gestion robuste des erreurs
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Définit le mode de récupération par défaut sur les tableaux associatifs
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    // S'il existe un problème de connection, on obtient le message d'erreur
    catch(PDOException $ex) {
        // Enregistre l'erreur pour le débogage côté serveur, ne pas afficher l'erreur brute à l'utilisateur
        error_log('Erreur de connexion : ' . $ex->getMessage());
        // Envoie une réponse d'erreur 500 Internal Server Error
        http_response_code(500);
        // Assurez-vous que l'en-tête et le nettoyage sont faits AVANT d'envoyer l'erreur JSON
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode(['error' => 'La connexion à la base de données a échoué. Veuillez réessayer plus tard.']);
        exit(); // Crucial : Arrête l'exécution du script si la connexion échoue

    }
    return $conn;
}

// Vérifie si la requête est POST ou GET pour déterminer comment récupérer les paramètres
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['elemid'])) {
    $elemeid = $_GET['elemid'];
    $conn = connect(); // Tente de se connecter à la base de données

    try {
        switch($elemeid) {
            // affichier les nombres de chaques risques lors de la selection d'une commune
            case 'information_commune':
                // Valide et nettoie l'entrée pour prévenir l'injection SQL
                $id_commune = filter_var($_GET['id_commune'], FILTER_VALIDATE_INT);

                if ($id_commune === false) {
                    http_response_code(400); // Requête incorrecte
                    header('Content-Type: application/json'); // Assurez-vous que l'en-tête est là
                    ob_clean();
                    echo json_encode(['error' => 'ID de commune invalide.']);
                    exit();
                }

                $sql = 'SELECT
                            COUNT(CASE WHEN risques = \'Inondation\' THEN 1 END) AS tt_inondation,
                            COUNT(CASE WHEN risques = \'Erosion\' THEN 1 END) AS tt_erosion,
                            COUNT(CASE WHEN risques = \'Eboulement\' THEN 1 END) AS tt_eboulement,
                            COUNT(CASE WHEN risques = \'Effondrement\' THEN 1 END) AS tt_effondrement,
                            COUNT(*) AS tt_risques
                        FROM informations
                        WHERE commune = :id_commune';

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_commune', $id_commune, PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch(); // Récupère la seule ligne de résultat

                header('Content-Type: application/json');
                ob_clean();
                echo json_encode([
                    'tt_inondation' => (int)$row['tt_inondation'], // Convertit en entier pour assurer une sortie numérique
                    'tt_erosion' => (int)$row['tt_erosion'],
                    'tt_eboulement' => (int)$row['tt_eboulement'],
                    'tt_effondrement' => (int)$row['tt_effondrement'],
                    'tt_risques' => (int)$row['tt_risques']
                ], JSON_NUMERIC_CHECK); // S'assure que les nombres ne sont pas retournés comme des chaînes
                break;

            case 'get_risks_coordinates':
                // Modification : si id_commune est 'all', on sélectionne tous les risques
                $id_commune = $_GET['id_commune'];
                $params = [];
                $where_clause = '';

                if ($id_commune !== 'all') {
                    $id_commune = filter_var($id_commune, FILTER_VALIDATE_INT);
                    if ($id_commune === false) {
                        http_response_code(400);
                        header('Content-Type: application/json');
                        ob_clean();
                        echo json_encode(['error' => 'ID de commune invalide pour les risques.']);
                        exit();
                    }
                    $where_clause = ' WHERE commune = :id_commune';
                    $params[':id_commune'] = $id_commune;
                }

                $sql = 'SELECT
                            id as id,
                            risques,
                            description,
                            fichier,
                            latitude,
                            longitude,
                            new_statut, -- NOUVEAU: Récupère le statut
                            date, -- NOUVEAU: Récupère la date
                            quartier, -- NOUVEAU: Récupère le quartier
                            recommandation -- NOUVEAU: Récupère la recommandation
                        FROM informations' . $where_clause;

                $stmt = $conn->prepare($sql);
                $stmt->execute($params);

                $risks_data = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $risks_data[] = [
                        'id' => (int)$row['id'],
                        'risques' => $row['risques'],
                        'description' => $row['description'],
                        'fichier' => $row['fichier'],
                        'latitude' => (float)$row['latitude'],
                        'longitude' => (float)$row['longitude'],
                        'new_statut' => $row['new_statut'], // Ajout du statut
                        'date' => $row['date'], // Ajout de la date
                        'quartier' => $row['quartier'], // Ajout du quartier
                        'recommandation' => $row['recommandation'] // Ajout de la recommandation
                    ];
                }

                header('Content-Type: application/json');
                ob_clean();
                echo json_encode($risks_data, JSON_NUMERIC_CHECK);
                break;

            case 'zoom_commune':
                // Valide et nettoie l'entrée pour prévenir l'injection SQL
                $id_commune = filter_var($_GET['id_commune'], FILTER_VALIDATE_INT);

                if ($id_commune === false) {
                    http_response_code(400); // Requête incorrecte
                    header('Content-Type: application/json');
                    ob_clean();
                    echo json_encode(['error' => 'ID de commune invalide.']);
                    exit();
                }

                $sql = 'SELECT id, public.ST_AsGeoJSON(ST_Transform(geom, 4326)) as geom, commune as nom_commune
                        FROM public.communes_abidjan
                        WHERE id = :id_commune';

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_commune', $id_commune, PDO::PARAM_INT);
                $stmt->execute();

                $geojson = [
                    'type' => 'FeatureCollection',
                    'features' => []
                ];

                while($row = $stmt->fetch()){
                    $feature = [
                        'type' => 'Feature',
                        'geometry' => json_decode($row['geom'], true),
                        'properties' => [
                            'id' => $row['id'],
                            'nom_commune' => $row['nom_commune']
                        ]
                    ];
                    array_push($geojson['features'], $feature);
                }

                header('Content-Type: application/json');
                ob_clean();
                echo json_encode($geojson, JSON_NUMERIC_CHECK);
                break;

            // <<< NOUVELLE CASE : Récupérer tous les risques pour l'affichage initial
            // Cette case est redondante si get_risks_coordinates gère déjà 'all'
            // case 'get_all_risks':
            //     $sql = 'SELECT
            //                 id as id_information,
            //                 risques,
            //                 description,
            //                 fichier,
            //                 latitude,
            //                 longitude,
            //                 statut,
            //                 date,
            //                 quartier,
            //                 recommandation
            //             FROM informations';

            //     $stmt = $conn->prepare($sql);
            //     $stmt->execute();

            //     $all_risks_data = [];
            //     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //         $all_risks_data[] = [
            //             'id_information' => (int)$row['id_information'],
            //             'risques' => $row['risques'],
            //             'description' => $row['description'],
            //             'fichier' => $row['fichier'],
            //             'latitude' => (float)$row['latitude'],
            //             'longitude' => (float)$row['longitude'],
            //             'statut' => $row['statut'],
            //             'date' => $row['date'],
            //             'quartier' => $row['quartier'],
            //             'recommandation' => $row['recommandation']
            //         ];
            //     }

            //     header('Content-Type: application/json');
            //     ob_clean();
            //     echo json_encode($all_risks_data, JSON_NUMERIC_CHECK);
            //     break;
            // >>> FIN NOUVELLE CASE

            // <<< NOUVELLE CASE : Récupérer les détails complets d'un risque
            case 'get_risk_details':
                $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

                if ($id === false) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    ob_clean();
                    echo json_encode(['success' => false, 'error' => 'ID d\'information invalide.']);
                    exit();
                }

                $sql = 'SELECT
                            id as id,
                            risques,
                            description,
                            fichier,
                            latitude,
                            longitude,
                            new_statut,
                            date,
                            quartier,
                            commune, -- Ajout de la commune
                            recommandation -- Ajout de la recommandation
                        FROM informations
                        WHERE id = :id';

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $risk_details = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($risk_details) {
                    header('Content-Type: application/json');
                    ob_clean();
                    echo json_encode(['success' => true, 'data' => $risk_details], JSON_NUMERIC_CHECK);
                } else {
                    http_response_code(404); // Not Found
                    header('Content-Type: application/json');
                    ob_clean();
                    echo json_encode(['success' => false, 'error' => 'Détails du risque non trouvés.']);
                }
                break;
            // >>> FIN NOUVELLE CASE

            // <<< NOUVELLE CASE : Récupérer les risques récents (pour le clignotement)
            case 'get_recent_risks':
                // Vous pouvez définir une période de temps pour les risques "récents"
                // Par exemple, les risques ajoutés ou mis à jour au cours des 5 dernières minutes
                $time_threshold = date('Y-m-d H:i:s', strtotime('-5 minutes')); // Ajustez cette durée si nécessaire

                $sql = 'SELECT
                            id as id,
                            risques,
                            description,
                            fichier,
                            latitude,
                            longitude,
                            new_statut,
                            date,
                            quartier,
                            recommandation
                        FROM informations
                        WHERE date >= :time_threshold AND new_statut = \'non_traite\''; // Seulement les non traités récents

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':time_threshold', $time_threshold);
                $stmt->execute();

                $recent_risks = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $recent_risks[] = [
                        'id' => (int)$row['id'],
                        'risques' => $row['risques'],
                        'description' => $row['description'],
                        'fichier' => $row['fichier'],
                        'latitude' => (float)$row['latitude'],
                        'longitude' => (float)$row['longitude'],
                        'new_statut' => $row['new_statut'],
                        'date' => $row['date'],
                        'quartier' => $row['quartier'],
                        'recommandation' => $row['recommandation']
                    ];
                }

                header('Content-Type: application/json');
                ob_clean();
                echo json_encode(['success' => true, 'data' => $recent_risks], JSON_NUMERIC_CHECK);
                break;
            // >>> FIN NOUVELLE CASE

            default:
                // Gère gracieusement les elemeid non supportés
                http_response_code(400); // Requête incorrecte
                header('Content-Type: application/json');
                ob_clean();
                echo json_encode(['error' => 'ID d\'élément non supporté.']);
                exit();
        }
    } catch (PDOException $e) {
        // Capture toutes les erreurs liées à la base de données pendant l'exécution de la requête
        error_log('Erreur de base de données : ' . $e->getMessage());
        http_response_code(500); // Erreur interne du serveur
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode(['error' => 'Une erreur de base de données est survenue.']);
    } finally {
        if (isset($stmt) && $stmt instanceof PDOStatement) {
            $stmt->closeCursor();
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['elemid'])) {
    // <<< NOUVELLE SECTION : Gérer les requêtes POST
    $elemeid = $_GET['elemid'];
    $conn = connect(); // Tente de se connecter à la base de données

    try {
        switch ($elemeid) {
            case 'update_risk_status':
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $recommandation = filter_input(INPUT_POST, 'recommandation', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $new_statut = filter_input(INPUT_POST, 'new_statut', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

                if ($id === false || !$recommandation || !$new_statut) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    ob_clean();
                    echo json_encode(['success' => false, 'error' => 'Données de mise à jour invalides.']);
                    exit();
                }

                $sql = 'UPDATE informations
                        SET recommandation = :recommandation, new_statut = :new_statut, last_updated = NOW()
                        WHERE id = :id';

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':recommandation', $recommandation);
                $stmt->bindParam(':new_statut', $new_statut);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    header('Content-Type: application/json');
                    ob_clean();
                    echo json_encode(['success' => true, 'message' => 'new_Statut et recommandation mis à jour.']);
                } else {
                    http_response_code(500);
                    header('Content-Type: application/json');
                    ob_clean();
                    echo json_encode(['success' => false, 'error' => 'Échec de la mise à jour en base de données.']);
                }
                break;

            default:
                http_response_code(400);
                header('Content-Type: application/json');
                ob_clean();
                echo json_encode(['error' => 'ID d\'élément POST non supporté.']);
                exit();
        }
    } catch (PDOException $e) {
        error_log('Erreur de base de données (POST) : ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Une erreur de base de données est survenue lors du traitement POST.']);
    } finally {
        if (isset($stmt) && $stmt instanceof PDOStatement) {
            $stmt->closeCursor();
        }
    }

} else {
    // Si le paramètre 'elemid' est manquant ou la méthode HTTP n'est pas supportée
    http_response_code(400); // Requête incorrecte
    header('Content-Type: application/json');
    ob_clean();
    echo json_encode(['error' => 'Paramètre elemid manquant ou méthode HTTP non supportée.']);
}

?>