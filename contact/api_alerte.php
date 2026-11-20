<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Api-Key');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config.php';

$headerKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!hash_equals(MOBILE_API_KEY, $headerKey)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Non autorisé.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}

$commune     = $_POST['commune']     ?? '';
$quartier    = $_POST['quartier']    ?? '';
$risques     = $_POST['risque']      ?? '';
$description = $_POST['description'] ?? '';
$date        = !empty($_POST['date']) ? $_POST['date'] : null;
$longitude   = $_POST['longitude']   ?? '';
$latitude    = $_POST['latitude']    ?? '';

$types_valides      = ['Inondation', 'Erosion', 'Eboulement', 'Effondrement'];
$extensions_valides  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'webm'];
$taille_max          = 15 * 1024 * 1024; // 15 Mo

if (!in_array($risques, $types_valides, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Veuillez sélectionner un type de risque valide.']);
    exit();
}

if (empty($_FILES['file']['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Veuillez joindre un fichier.']);
    exit();
}

if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Erreur lors du téléchargement du fichier."]);
    exit();
}

if ($_FILES['file']['size'] > $taille_max) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Fichier trop volumineux (15 Mo maximum).']);
    exit();
}

$file = basename($_FILES['file']['name']);
$ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
if (!in_array($ext, $extensions_valides, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé. Formats acceptés : photo (jpg, png, gif, webp) ou vidéo (mp4, mov, webm).']);
    exit();
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Échec de la connexion.']);
    exit();
}

$targetDir = __DIR__ . '/informations/';
if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetDir . $file)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors du déplacement du fichier.']);
    exit();
}

// Copie dans la galerie selon l'année de la date saisie, comme sur le formulaire web
$annee      = $date ? date('Y', strtotime($date)) : date('Y');
$galleryDir = __DIR__ . '/../documentation/data/' . $annee . '/';
if (!is_dir($galleryDir)) mkdir($galleryDir, 0777, true);
copy($targetDir . $file, $galleryDir . $file);

try {
    $dateExpr = $date ? ':date' : 'NOW()';
    $stmt = $pdo->prepare("
        INSERT INTO informations (commune, quartier, risques, description, date, fichier, longitude, latitude)
        VALUES (:commune, :quartier, :risques, :description, $dateExpr, :fichier, :longitude, :latitude)
    ");
    $stmt->bindParam(':commune',     $commune);
    $stmt->bindParam(':quartier',    $quartier);
    $stmt->bindParam(':risques',     $risques);
    $stmt->bindParam(':description', $description);
    if ($date) $stmt->bindValue(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':fichier',     $file);
    $stmt->bindParam(':longitude',   $longitude);
    $stmt->bindParam(':latitude',    $latitude);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Alerte enregistrée avec succès.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Erreur lors de l'insertion des données."]);
}
