<?php
// ============================================================================
// EXPORT D'UN BILAN AU FORMAT WORD (.doc) OU PDF
// ============================================================================
require_once __DIR__ . '/../config.php';

$id     = (int)($_GET['id'] ?? 0);
$format = $_GET['format'] ?? 'pdf';

if ($id <= 0) {
    die('Bilan introuvable.');
}

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . htmlspecialchars($e->getMessage()));
}

$stmt = $pdo->prepare("SELECT * FROM bilans WHERE id_bilan = :id");
$stmt->execute([':id' => $id]);
$bilan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bilan) {
    die('Bilan introuvable.');
}

/**
 * Formate un tableau PostgreSQL TEXT[] (ex: {"Pluies","Caniveaux"}) en texte lisible
 */
function formaterListePg(?string $pgArray): string {
    if (!$pgArray || $pgArray === '{}' || trim($pgArray) === '') {
        return 'Non renseigné';
    }
    $interieur = trim($pgArray, '{}');
    $elements  = str_getcsv($interieur, ',', '"', '\\');
    $nettoyes  = array_filter(array_map('trim', $elements), fn($v) => $v !== '');
    return !empty($nettoyes) ? implode(', ', $nettoyes) : 'Non renseigné';
}

$dateAffichage = !empty($bilan['date_debut'])
    ? date('d/m/Y H:i', strtotime($bilan['date_debut']))
    : 'Date non renseignée';

// Construction des sections du bilan (réutilisées pour le Word et le PDF)
$sections = [
    'Identification' => [
        'Référence'  => '#BIL-' . $id,
        'Statut'     => $bilan['statut_bilan'],
        'Rédacteur'  => $bilan['redacteur_nom'] . ' (' . ($bilan['redacteur_fonction'] ?: 'Fonction non précisée') . ' — ' . ($bilan['organisme'] ?: 'Organisme non précisé') . ')',
    ],
    'Position géographique et épisode' => [
        'Commune'           => $bilan['commune'],
        'Quartier'          => $bilan['quartier'],
        'Date de début'     => $dateAffichage,
        'Date de fin'       => !empty($bilan['date_fin']) ? date('d/m/Y H:i', strtotime($bilan['date_fin'])) : 'Non renseignée',
        'Repère précis'     => $bilan['repere_precis'] ?: 'Non précisé',
        'Type de milieu'    => $bilan['type_milieu'] ?: 'Non précisé',
        'Coordonnées GPS'   => $bilan['latitude'] . ', ' . $bilan['longitude'],
    ],
    'Causes identifiées' => [
        'Facteurs météo / hydrologiques'    => formaterListePg($bilan['causes_meteo']),
        'Drainage / urbanisation'           => formaterListePg($bilan['causes_urbanisme_drainage']),
        'Analyse complémentaire'            => $bilan['details_causes'] ?: 'Aucune précision supplémentaire',
    ],
    'Dégâts causés' => [
        'Décès'                       => $bilan['nb_deces'],
        'Blessés'                     => $bilan['nb_blesses'],
        'Disparus'                    => $bilan['nb_disparus'],
        'Ménages sinistrés'           => $bilan['nb_menages_sinistres'],
        'Personnes déplacées'        => $bilan['nb_personnes_deplacees'],
        'Habitations touchées'       => $bilan['nb_habitations_touchees'],
        'Habitations détruites'      => $bilan['nb_habitations_detruites'],
        'Infrastructures endommagées' => formaterListePg($bilan['infrastructures_endommagees']),
        'Risques sanitaires'          => formaterListePg($bilan['risques_sanitaires']),
        'Activités touchées'          => formaterListePg($bilan['activites_touchees']),
        'Estimation des pertes'       => $bilan['estimation_pertes'] ?: 'Non renseigné',
    ],
    'Actions pendant la crise' => [
        'Actions d\'urgence'    => formaterListePg($bilan['actions_urgence']),
        'Acteurs mobilisés'     => formaterListePg($bilan['acteurs_mobilises']),
        'Moyens matériels'      => $bilan['moyens_materiels'] ?: 'Non précisé',
        'Difficultés'           => $bilan['difficultes_intervention'] ?: 'Aucune signalée',
    ],
    'Actions post-crise' => [
        'Actions menées' => formaterListePg($bilan['actions_post_crise']),
        'Avancement'     => $bilan['avancement_post_crise'],
    ],
    'Mesures préventives' => [
        'Mesures structurelles'     => formaterListePg($bilan['mesures_structurelles']),
        'Mesures non structurelles' => formaterListePg($bilan['mesures_non_structurelles']),
        'Action prioritaire'        => $bilan['action_prioritaire'] ?: 'Non précisée',
        'Responsable'               => $bilan['responsable_action'] ?: 'Non précisé',
        'Horizon de mise en œuvre'  => $bilan['horizon_action'] ?: 'Non précisé',
    ],
];

$titre = 'Bilan #BIL-' . $id . ' — ' . $bilan['commune'] . ' (' . $bilan['quartier'] . ')';

if ($format === 'word') {
    // ========================================================================
    // EXPORT WORD : document HTML servi avec le type MIME Word (ouvre directement dans Word)
    // ========================================================================
    header('Content-Type: application/msword; charset=utf-8');
    header('Content-Disposition: attachment; filename="bilan_BIL-' . $id . '.doc"');
    ?>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif;">
    <h1 style="color:#0f4c81;"><?= htmlspecialchars($titre) ?></h1>
    <?php foreach ($sections as $titreSection => $champs): ?>
        <h2 style="color:#0f4c81; border-bottom:1px solid #0f4c81;"><?= htmlspecialchars($titreSection) ?></h2>
        <table style="width:100%; border-collapse: collapse; margin-bottom:16px;">
            <?php foreach ($champs as $label => $valeur): ?>
                <tr>
                    <td style="padding:4px 8px; font-weight:bold; width:220px; vertical-align:top;"><?= htmlspecialchars($label) ?></td>
                    <td style="padding:4px 8px;"><?= htmlspecialchars((string)$valeur) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
</body>
</html>
    <?php
    exit;
}

// ============================================================================
// EXPORT PDF (via FPDF)
// ============================================================================
require_once __DIR__ . '/../vendor/autoload.php';

// FPDF utilise l'encodage Windows-1252 : conversion des accents avant affichage
function v1252(string $texte): string {
    $converti = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $texte);
    return $converti !== false ? $converti : $texte;
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(15, 76, 129);
$pdf->MultiCell(0, 10, v1252($titre));
$pdf->Ln(2);

foreach ($sections as $titreSection => $champs) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(15, 76, 129);
    $pdf->MultiCell(0, 8, v1252($titreSection));
    $pdf->SetDrawColor(15, 76, 129);
    $pdf->Line($pdf->GetX(), $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(2);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(30, 41, 59);
    foreach ($champs as $label => $valeur) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->MultiCell(0, 6, v1252($label . ' :'));
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 6, v1252((string)$valeur));
        $pdf->Ln(1);
    }
    $pdf->Ln(3);
}

$pdf->Output('D', 'bilan_BIL-' . $id . '.pdf');
exit;
