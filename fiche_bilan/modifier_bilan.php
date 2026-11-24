<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<?php
// ============================================================================
// ÉTAPE 1 : CONNEXION À LA BASE POSTGRESQL (VIA PDO) ET CHARGEMENT DU BILAN
// ============================================================================
require_once __DIR__ . '/../config.php';

$erreur = '';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . htmlspecialchars($e->getMessage()));
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM bilans WHERE id_bilan = :id");
$stmt->execute([':id' => $id]);
$bilan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bilan) {
    header('Location: index.php');
    exit;
}

// Liste des communes proposée dans la liste déroulante
$communesListe = [
    'Abobo',
    'Adjamé',
    'Attécoubé',
    'Cocody',
    'Koumassi',
    'Marcory',
    'Plateau',
    'Port-Bouët',
    'Treichville',
    'Yopougon'
];

/**
 * Convertit un tableau PHP (cases à cocher + éventuel champ texte "autre")
 * en littéral de tableau PostgreSQL TEXT[], ex : {"Pluies","Caniveaux"}
 */
function toPgArray(?array $arr, ?string $autre = null): ?string {
    $items = $arr ?? [];
    if ($autre !== null && trim($autre) !== '') {
        $items[] = trim($autre);
    }
    if (empty($items)) {
        return null;
    }
    $echappes = array_map(function ($valeur) {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], trim((string)$valeur)) . '"';
    }, $items);
    return '{' . implode(',', $echappes) . '}';
}

/**
 * Convertit un littéral de tableau PostgreSQL TEXT[] en tableau PHP (pour pré-remplir le formulaire)
 */
function depuisPgArray(?string $pgArray): array {
    if (!$pgArray || $pgArray === '{}' || trim($pgArray) === '') {
        return [];
    }
    $interieur = trim($pgArray, '{}');
    $elements  = str_getcsv($interieur, ',', '"', '\\');
    return array_values(array_filter(array_map('trim', $elements), fn($v) => $v !== ''));
}

/** Affiche l'attribut checked si $valeur fait partie du tableau déjà enregistré */
function coche(string $valeur, array $valeursSauvegardees): string {
    return in_array($valeur, $valeursSauvegardees, true) ? 'checked' : '';
}

/** Valeurs enregistrées qui ne correspondent à aucune case prédéfinie -> champ "autre" */
function valeursAutres(array $valeursSauvegardees, array $valeursConnues): string {
    return implode(', ', array_diff($valeursSauvegardees, $valeursConnues));
}

$causesMeteoSauvees              = depuisPgArray($bilan['causes_meteo']);
$causesUrbanismeSauvees          = depuisPgArray($bilan['causes_urbanisme_drainage']);
$infrastructuresSauvees          = depuisPgArray($bilan['infrastructures_endommagees']);
$risquesSauves                   = depuisPgArray($bilan['risques_sanitaires']);
$activitesSauvees                = depuisPgArray($bilan['activites_touchees']);
$actionsUrgenceSauvees           = depuisPgArray($bilan['actions_urgence']);
$acteursSauves                   = depuisPgArray($bilan['acteurs_mobilises']);
$actionsPostCriseSauvees         = depuisPgArray($bilan['actions_post_crise']);
$mesuresStructurellesSauvees     = depuisPgArray($bilan['mesures_structurelles']);
$mesuresNonStructurellesSauvees  = depuisPgArray($bilan['mesures_non_structurelles']);

$infrastructuresConnues = ['Voirie / routes', 'Ponts', 'Réseau drainage', "Réseaux d'eau", 'Réseaux électrique', 'Réseaux de Télécoms', 'Écoles', 'Centres de santé'];
$risquesConnus          = ['Eaux stagnantes', 'Risque épidémique', 'Rupture eau potable'];
$activitesConnues       = ['Commerces', 'Marchés', 'Cultures', 'Elevage', 'Sédimentation', 'Pollution'];
$actionsUrgenceConnues  = ['Évacuation populations', "Hébergement d'urgence", 'Distribution vivres / eau', 'Assistance médicale', 'Sécurisation / déviation'];
$acteursConnus          = ['Protection civile', "Forces de l'ordre", 'GSPM', 'Services techniques', 'Organismes drainage / assainissement', 'ONG', 'Croix-Rouge', 'Bénévoles'];
$actionsPostCriseConnues = ['Déblaiement boues et déchets', 'Curage caniveaux et bassins', 'Réparation urgence réseaux', 'Évaluation officielle dégâts', 'Aide financière', 'Indemnisation'];
$mesuresStructurellesConnues    = ['Création caniveaux', 'Calibrage caniveaux', 'Curage préventif régulier', 'Réhabilitation ouvrages', 'Aménagement bassins versants'];
$mesuresNonStructurellesConnues = ['Libération zones inondables', 'Collecte déchets', 'Sensibilisation', 'Amélioration alerte précoce', 'Etude technique', 'Cartographie des risques'];

// Reconstitution du texte "pertes / besoins urgents" précédemment fusionné à l'enregistrement
$estimationPertesExistante = '';
$besoinsUrgentsExistants   = '';
if (!empty($bilan['estimation_pertes'])) {
    if (preg_match('/^(.*)\|\s*Besoins urgents\s*:\s*(.*)$/s', $bilan['estimation_pertes'], $m)) {
        $estimationPertesExistante = trim(preg_replace('/^Pertes\s*:\s*/', '', $m[1]));
        $besoinsUrgentsExistants   = trim($m[2]);
    } elseif (preg_match('/^Besoins urgents\s*:\s*(.*)$/s', $bilan['estimation_pertes'], $m)) {
        $besoinsUrgentsExistants = trim($m[1]);
    } else {
        $estimationPertesExistante = $bilan['estimation_pertes'];
    }
}

// ============================================================================
// ÉTAPE 2 : TRAITEMENT DE LA MISE À JOUR ET REDIRECTION VERS INDEX.PHP
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupération de la commune choisie (ou saisie libre si "Autre")
        $communeChoisie = $_POST['commune'] ?? '';
        $communeFinale  = ($communeChoisie === 'Autre')
            ? trim($_POST['commune_autre'] ?? '')
            : trim($communeChoisie);

        if ($communeFinale === '') {
            throw new Exception("Veuillez sélectionner ou préciser une commune.");
        }

        // Conversion des dates <input type="datetime-local"> au format SQL TIMESTAMP
        $dateDebut = !empty($_POST['date_debut'])
            ? str_replace('T', ' ', $_POST['date_debut']) . ':00'
            : null;

        $dateFin = !empty($_POST['date_fin'])
            ? str_replace('T', ' ', $_POST['date_fin']) . ':00'
            : null;

        // Regroupement de l'estimation des pertes et des besoins urgents dans `estimation_pertes`
        $pertes  = trim($_POST['estimation_pertes'] ?? '');
        $besoins = trim($_POST['besoins_urgents'] ?? '');
        $estimationFinale = null;
        if ($pertes !== '' && $besoins !== '') {
            $estimationFinale = "Pertes : " . $pertes . " | Besoins urgents : " . $besoins;
        } elseif ($pertes !== '') {
            $estimationFinale = $pertes;
        } elseif ($besoins !== '') {
            $estimationFinale = "Besoins urgents : " . $besoins;
        }

        $sql = "UPDATE bilans SET
            id_alerte = :id_alerte, redacteur_nom = :redacteur_nom, redacteur_fonction = :redacteur_fonction,
            organisme = :organisme, statut_bilan = :statut_bilan,
            date_debut = :date_debut, date_fin = :date_fin, commune = :commune, quartier = :quartier,
            repere_precis = :repere_precis, type_milieu = :type_milieu, latitude = :latitude, longitude = :longitude,
            causes_meteo = :causes_meteo, causes_urbanisme_drainage = :causes_urbanisme_drainage, details_causes = :details_causes,
            nb_deces = :nb_deces, nb_blesses = :nb_blesses, nb_disparus = :nb_disparus,
            nb_menages_sinistres = :nb_menages_sinistres, nb_personnes_deplacees = :nb_personnes_deplacees,
            risques_sanitaires = :risques_sanitaires, infrastructures_endommagees = :infrastructures_endommagees,
            nb_habitations_touchees = :nb_habitations_touchees, nb_habitations_detruites = :nb_habitations_detruites,
            activites_touchees = :activites_touchees, estimation_pertes = :estimation_pertes,
            actions_urgence = :actions_urgence, acteurs_mobilises = :acteurs_mobilises,
            moyens_materiels = :moyens_materiels, difficultes_intervention = :difficultes_intervention,
            actions_post_crise = :actions_post_crise, avancement_post_crise = :avancement_post_crise,
            mesures_structurelles = :mesures_structurelles, mesures_non_structurelles = :mesures_non_structurelles,
            action_prioritaire = :action_prioritaire, responsable_action = :responsable_action, horizon_action = :horizon_action
        WHERE id_bilan = :id_bilan";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_alerte'                   => !empty($_POST['id_alerte']) ? (int)$_POST['id_alerte'] : null,
            ':redacteur_nom'               => trim($_POST['redacteur_nom']),
            ':redacteur_fonction'          => trim($_POST['redacteur_fonction'] ?? '') ?: null,
            ':organisme'                   => trim($_POST['organisme'] ?? '') ?: null,
            ':statut_bilan'                => $_POST['statut_bilan'] ?? 'Brouillon',
            ':date_debut'                  => $dateDebut,
            ':date_fin'                    => $dateFin,
            ':commune'                     => $communeFinale,
            ':quartier'                    => trim($_POST['quartier']),
            ':repere_precis'               => trim($_POST['repere_precis'] ?? '') ?: null,
            ':type_milieu'                 => $_POST['type_milieu'] ?? null,
            ':latitude'                    => (float)$_POST['latitude'],
            ':longitude'                   => (float)$_POST['longitude'],
            ':causes_meteo'                => toPgArray($_POST['causes_meteo'] ?? []),
            ':causes_urbanisme_drainage'   => toPgArray($_POST['causes_urbanisme_drainage'] ?? []),
            ':details_causes'              => trim($_POST['details_causes'] ?? '') ?: null,
            ':nb_deces'                    => max(0, (int)($_POST['nb_deces'] ?? 0)),
            ':nb_blesses'                  => max(0, (int)($_POST['nb_blesses'] ?? 0)),
            ':nb_disparus'                 => max(0, (int)($_POST['nb_disparus'] ?? 0)),
            ':nb_menages_sinistres'        => max(0, (int)($_POST['nb_menages_sinistres'] ?? 0)),
            ':nb_personnes_deplacees'      => max(0, (int)($_POST['nb_personnes_deplacees'] ?? 0)),
            ':risques_sanitaires'          => toPgArray($_POST['risques_sanitaires'] ?? [], $_POST['autres_risques_sanitaires'] ?? null),
            ':infrastructures_endommagees' => toPgArray($_POST['infrastructures_endommagees'] ?? [], $_POST['autres_infrastructures'] ?? null),
            ':nb_habitations_touchees'     => max(0, (int)($_POST['nb_habitations_touchees'] ?? 0)),
            ':nb_habitations_detruites'    => max(0, (int)($_POST['nb_habitations_detruites'] ?? 0)),
            ':activites_touchees'          => toPgArray($_POST['activites_touchees'] ?? [], $_POST['autres_activites_environnement'] ?? null),
            ':estimation_pertes'           => $estimationFinale,
            ':actions_urgence'             => toPgArray($_POST['actions_urgence'] ?? [], $_POST['autres_actions_urgence'] ?? null),
            ':acteurs_mobilises'           => toPgArray($_POST['acteurs_mobilises'] ?? [], $_POST['autres_acteurs_mobilises'] ?? null),
            ':moyens_materiels'            => trim($_POST['moyens_materiels'] ?? '') ?: null,
            ':difficultes_intervention'    => trim($_POST['difficultes_intervention'] ?? '') ?: null,
            ':actions_post_crise'          => toPgArray($_POST['actions_post_crise'] ?? [], $_POST['autres_actions_post_crise'] ?? null),
            ':avancement_post_crise'       => $_POST['avancement_post_crise'] ?? 'Non engagées',
            ':mesures_structurelles'       => toPgArray($_POST['mesures_structurelles'] ?? [], $_POST['autres_mesures_structurelles'] ?? null),
            ':mesures_non_structurelles'   => toPgArray($_POST['mesures_non_structurelles'] ?? [], $_POST['autres_mesures_non_structurelles'] ?? null),
            ':action_prioritaire'          => trim($_POST['action_prioritaire'] ?? '') ?: null,
            ':responsable_action'          => trim($_POST['responsable_action'] ?? '') ?: null,
            ':horizon_action'              => $_POST['horizon_action'] ?? null,
            ':id_bilan'                    => $id,
        ]);

        header("Location: index.php?updated=1&highlight=" . $id);
        exit;
    } catch (Exception $e) {
        $erreur = "Impossible d'enregistrer les modifications : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le bilan</title>

    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">

    <!-- Bibliothèque cartographique Leaflet pour la position GPS et le marqueur déplaçable -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #fiche-bilan-form-page { font-family: system-ui, -apple-system, sans-serif; background: #f3f5f8; color: #1e293b; margin: 0; padding: 24px; }
        #fiche-bilan-form-page .container { max-width: 960px; margin: auto; background: #fff; padding: 26px 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        #fiche-bilan-form-page .top-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-bottom: 2px solid #0f4c81; padding-bottom: 12px; margin-bottom: 20px; }
        #fiche-bilan-form-page h1 { margin: 0; font-size: 1.45rem; color: #0f4c81; }
        #fiche-bilan-form-page .lien-retour { text-decoration: none; color: #0f4c81; font-weight: 600; border: 1px solid #0f4c81; padding: 7px 14px; border-radius: 6px; }
        #fiche-bilan-form-page .lien-retour:hover { background: #eff6ff; }
        #fiche-bilan-form-page fieldset { border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 20px; padding: 16px 20px; }
        #fiche-bilan-form-page legend { font-weight: 700; color: #0f4c81; padding: 0 8px; }
        #fiche-bilan-form-page .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        #fiche-bilan-form-page .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        #fiche-bilan-form-page .form-group { margin-bottom: 12px; }
        #fiche-bilan-form-page label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 0.9rem; }
        #fiche-bilan-form-page input[type="text"], #fiche-bilan-form-page input[type="number"], #fiche-bilan-form-page input[type="datetime-local"], #fiche-bilan-form-page select, #fiche-bilan-form-page textarea {
            width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit; background: #fff;
        }
        #fiche-bilan-form-page .checkbox-group label { font-weight: 400; display: flex; align-items: center; gap: 8px; margin-bottom: 6px; cursor: pointer; }
        #fiche-bilan-form-page .alert-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 18px; font-weight: 600; }
        #fiche-bilan-form-page .gps-barre { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 8px; }
        #fiche-bilan-form-page .btn-gps { background: #eff6ff; color: #0f4c81; border: 1px solid #0f4c81; padding: 7px 12px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        #fiche-bilan-form-page #map-picker { height: 270px; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 12px; }
        #fiche-bilan-form-page button[type="submit"] { background: #0f4c81; color: #fff; border: none; padding: 12px 24px; font-size: 1rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
        #fiche-bilan-form-page button[type="submit"]:hover { background: #09355b; }
        @media (max-width: 700px) { #fiche-bilan-form-page .grid-2, #fiche-bilan-form-page .grid-3 { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="skin-blue sidebar-mini">
<div class="wrapper">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><i class="fa fa-file-text-o"></i> Modifier le bilan</h1>
    </section>

    <section class="content">
<div id="fiche-bilan-form-page">
<div class="container">
    <!-- EN-TÊTE ET ACCÈS DIRECT À LA PAGE DES BILANS -->
    <div class="top-bar">
        <h1>Modifier le bilan #BIL-<?= $id ?></h1>
        <a class="lien-retour" href="index.php">Voir les bilans</a>
    </div>

    <?php if ($erreur): ?>
        <div class="alert-error"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <!-- ÉTAPE 1 : IDENTIFICATION DU RÉDACTEUR ET STATUT DU BILAN -->
        <fieldset>
            <legend>1. Identification du rédacteur et statut</legend>
            <div class="grid-3">
                <div class="form-group">
                    <label>Nom du rédacteur *</label>
                    <input type="text" name="redacteur_nom" required value="<?= htmlspecialchars($bilan['redacteur_nom']) ?>">
                </div>
                <div class="form-group">
                    <label>Fonction</label>
                    <input type="text" name="redacteur_fonction" placeholder="Ex: Responsable technique" value="<?= htmlspecialchars($bilan['redacteur_fonction'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Organisme / Structure</label>
                    <input type="text" name="organisme" placeholder="Ex: Mairie, ONAD, Protection Civile" value="<?= htmlspecialchars($bilan['organisme'] ?? '') ?>">
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>ID Alerte liée (optionnel)</label>
                    <input type="number" name="id_alerte" min="1" placeholder="Ex: 15" value="<?= htmlspecialchars($bilan['id_alerte'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Statut du bilan</label>
                    <select name="statut_bilan">
                        <option value="Brouillon" <?= $bilan['statut_bilan'] === 'Brouillon' ? 'selected' : '' ?>>Brouillon</option>
                        <option value="Partiel" <?= $bilan['statut_bilan'] === 'Partiel' ? 'selected' : '' ?>>Partiel (en cours d'évaluation)</option>
                        <option value="Consolidé" <?= $bilan['statut_bilan'] === 'Consolidé' ? 'selected' : '' ?>>Consolidé / Validé</option>
                    </select>
                </div>
            </div>
        </fieldset>

        <!-- ÉTAPE 2 : POSITION GÉOGRAPHIQUE, DATES ET COORDONNÉES (AUTO + DÉPLAÇABLE) -->
        <fieldset>
            <legend>2. Position géographique et épisode</legend>
            <div class="grid-2">
                <div class="form-group">
                    <label>Début de la crise *</label>
                    <input type="datetime-local" name="date_debut" required value="<?= !empty($bilan['date_debut']) ? date('Y-m-d\TH:i', strtotime($bilan['date_debut'])) : '' ?>">
                </div>
                <div class="form-group">
                    <label>Fin / Décrue de la crise</label>
                    <input type="datetime-local" name="date_fin" value="<?= !empty($bilan['date_fin']) ? date('Y-m-d\TH:i', strtotime($bilan['date_fin'])) : '' ?>">
                </div>
            </div>

            <div class="grid-3">
                <div class="form-group">
                    <label for="commune">Commune *</label>
                    <?php $communeEstConnue = in_array($bilan['commune'], $communesListe, true); ?>
                    <select id="commune" name="commune" required onchange="toggleAutreCommune()">
                        <option value="">-- Choisir une commune --</option>
                        <?php foreach ($communesListe as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= $bilan['commune'] === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                        <option value="Autre" <?= !$communeEstConnue ? 'selected' : '' ?>>Autre commune...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quartier *</label>
                    <input type="text" name="quartier" required value="<?= htmlspecialchars($bilan['quartier']) ?>">
                </div>
                <div class="form-group">
                    <label>Type de milieu</label>
                    <select name="type_milieu">
                        <?php foreach (['Bas-fond / zone marécageuse', 'Bassin versant', 'Bord de lagune', 'Quartier urbanisé', 'Voirie', 'Zone périurbaine', 'Zone agricole'] as $tm): ?>
                            <option value="<?= htmlspecialchars($tm) ?>" <?= $bilan['type_milieu'] === $tm ? 'selected' : '' ?>><?= htmlspecialchars($tm) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Champ affiché uniquement si "Autre commune..." est sélectionnée -->
            <div class="form-group" id="bloc-commune-autre" style="display: <?= !$communeEstConnue ? 'block' : 'none' ?>;">
                <label for="commune_autre">Préciser la commune *</label>
                <input type="text" id="commune_autre" name="commune_autre" placeholder="Nom de la commune" value="<?= !$communeEstConnue ? htmlspecialchars($bilan['commune']) : '' ?>">
            </div>

            <div class="form-group">
                <label>Repère précis / Rue</label>
                <input type="text" name="repere_precis" placeholder="Ex: Près du dalot / carrefour..." value="<?= htmlspecialchars($bilan['repere_precis'] ?? '') ?>">
            </div>

            <!-- Géolocalisation automatique + carte interactive avec marqueur déplaçable -->
            <div class="gps-barre">
                <button type="button" class="btn-gps" onclick="localiserAutomatiquement()">Actualiser ma position GPS</button>
                <span id="statut-gps" style="font-size: 0.85rem; color: #475569;">
                    Position actuelle du bilan : glisse le marqueur ou clique sur la carte pour corriger.
                </span>
            </div>
            <div id="map-picker"></div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="latitude">Latitude (WGS84) *</label>
                    <input type="text" id="latitude" name="latitude" required value="<?= htmlspecialchars($bilan['latitude']) ?>">
                </div>
                <div class="form-group">
                    <label for="longitude">Longitude (WGS84) *</label>
                    <input type="text" id="longitude" name="longitude" required value="<?= htmlspecialchars($bilan['longitude']) ?>">
                </div>
            </div>
        </fieldset>

        <!-- ÉTAPE 3 : CAUSES IDENTIFIÉES DE L'INONDATION -->
        <fieldset>
            <legend>3. Causes identifiées</legend>
            <div class="grid-2 checkbox-group">
                <div>
                    <label><strong>Facteurs météorologiques et hydrologiques :</strong></label>
                    <label><input type="checkbox" name="causes_meteo[]" value="Précipitations exceptionnelles" <?= coche('Précipitations exceptionnelles', $causesMeteoSauvees) ?>> Précipitations exceptionnelles</label>
                    <label><input type="checkbox" name="causes_meteo[]" value="Ruissellement intense amont" <?= coche('Ruissellement intense amont', $causesMeteoSauvees) ?>> Ruissellement intense depuis l'amont</label>
                    <label><input type="checkbox" name="causes_meteo[]" value="Débordement cours d'eau / lagune" <?= coche("Débordement cours d'eau / lagune", $causesMeteoSauvees) ?>> Débordement cours d'eau / lagune</label>
                    <label><input type="checkbox" name="causes_meteo[]" value="Saturation des sols" <?= coche('Saturation des sols', $causesMeteoSauvees) ?>> Saturation des sols ou nappe phréatique</label>
                </div>
                <div>
                    <label><strong>Drainage, urbanisation et entretien :</strong></label>
                    <label><input type="checkbox" name="causes_urbanisme_drainage[]" value="Obstruction caniveaux / dalots" <?= coche('Obstruction caniveaux / dalots', $causesUrbanismeSauvees) ?>> Obstruction des caniveaux</label>
                    <label><input type="checkbox" name="causes_urbanisme_drainage[]" value="Insuffisance drainage" <?= coche('Insuffisance drainage', $causesUrbanismeSauvees) ?>> Insuffisance ou absence de drainage</label>
                    <label><input type="checkbox" name="causes_urbanisme_drainage[]" value="Construction en zone inondable" <?= coche('Construction en zone inondable', $causesUrbanismeSauvees) ?>> Construction en zone inondable</label>
                    <label><input type="checkbox" name="causes_urbanisme_drainage[]" value="Rupture ouvrage hydraulique" <?= coche('Rupture ouvrage hydraulique', $causesUrbanismeSauvees) ?>> Dégradation  rupture d'ouvrage</label>
                </div>
            </div>
            <div class="form-group">
                <label>Analyse complémentaire des causes</label>
                <textarea name="details_causes" rows="2"><?= htmlspecialchars($bilan['details_causes'] ?? '') ?></textarea>
            </div>
        </fieldset>

        <!-- ÉTAPE 4 : BILAN DES DÉGÂTS CAUSÉS -->
        <fieldset>
            <legend>4. Dégâts causés</legend>
            <div class="grid-3">
                <div class="form-group"><label>Décès</label><input type="number" name="nb_deces" value="<?= (int)$bilan['nb_deces'] ?>" min="0"></div>
                <div class="form-group"><label>Blessés</label><input type="number" name="nb_blesses" value="<?= (int)$bilan['nb_blesses'] ?>" min="0"></div>
                <div class="form-group"><label>Disparus</label><input type="number" name="nb_disparus" value="<?= (int)$bilan['nb_disparus'] ?>" min="0"></div>
            </div>
            <div class="grid-2">
                <div class="form-group"><label>Ménages sinistrés</label><input type="number" name="nb_menages_sinistres" value="<?= (int)$bilan['nb_menages_sinistres'] ?>" min="0"></div>
                <div class="form-group"><label>Personnes déplacées / évacuées</label><input type="number" name="nb_personnes_deplacees" value="<?= (int)$bilan['nb_personnes_deplacees'] ?>" min="0"></div>
                <div class="form-group"><label>Habitations touchées</label><input type="number" name="nb_habitations_touchees" value="<?= (int)$bilan['nb_habitations_touchees'] ?>" min="0"></div>
                <div class="form-group"><label>Habitations détruites / effondrées</label><input type="number" name="nb_habitations_detruites" value="<?= (int)$bilan['nb_habitations_detruites'] ?>" min="0"></div>
            </div>
            <div class="grid-3 checkbox-group">
                <div>
                    <label><strong>Infrastructures endommagées :</strong></label>
                    <?php foreach ($infrastructuresConnues as $val): ?>
                        <label><input type="checkbox" name="infrastructures_endommagees[]" value="<?= htmlspecialchars($val) ?>" <?= coche($val, $infrastructuresSauvees) ?>> <?= htmlspecialchars($val) ?></label>
                    <?php endforeach; ?>
                    <input type="text" id="autres_infrastructures" name="autres_infrastructures" placeholder="Autres infrastructures" value="<?= htmlspecialchars(valeursAutres($infrastructuresSauvees, $infrastructuresConnues)) ?>">
                </div>
                <div>
                    <label><strong>Risques sanitaires :</strong></label>
                    <?php foreach ($risquesConnus as $val): ?>
                        <label><input type="checkbox" name="risques_sanitaires[]" value="<?= htmlspecialchars($val) ?>" <?= coche($val, $risquesSauves) ?>> <?= htmlspecialchars($val) ?></label>
                    <?php endforeach; ?>
                    <input type="text" id="autres_risques_sanitaires" name="autres_risques_sanitaires" placeholder="Autres risques sanitaires" value="<?= htmlspecialchars(valeursAutres($risquesSauves, $risquesConnus)) ?>">
                </div>
                <div>
                    <label><strong>Activités et environnement :</strong></label>
                    <?php foreach ($activitesConnues as $val): ?>
                        <label><input type="checkbox" name="activites_touchees[]" value="<?= htmlspecialchars($val) ?>" <?= coche($val, $activitesSauvees) ?>> <?= htmlspecialchars($val) ?></label>
                    <?php endforeach; ?>
                    <input type="text" id="autres_activites_environnement" name="autres_activites_environnement" placeholder="Autres activités / environnement" value="<?= htmlspecialchars(valeursAutres($activitesSauvees, $activitesConnues)) ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Estimation des pertes</label>
                <textarea name="estimation_pertes" rows="2"><?= htmlspecialchars($estimationPertesExistante) ?></textarea>
            </div>
            <div class="form-group">
                <label>Besoins urgents</label>
                <textarea name="besoins_urgents" rows="2"><?= htmlspecialchars($besoinsUrgentsExistants) ?></textarea>
            </div>
        </fieldset>

        <!-- ÉTAPE 5 : ACTIONS PRISES PENDANT LA MANIFESTATION DU PHÉNOMÈNE -->
        <fieldset>
            <legend>5. Actions prises pendant la manifestation du phénomène</legend>
            <div class="grid-2 checkbox-group">
                <div>
                    <label><strong>Actions d'urgence :</strong></label>
                    <?php foreach ($actionsUrgenceConnues as $val): ?>
                        <label><input type="checkbox" name="actions_urgence[]" value="<?= htmlspecialchars($val) ?>" <?= coche($val, $actionsUrgenceSauvees) ?>> <?= htmlspecialchars($val) ?></label>
                    <?php endforeach; ?>
                    <input type="text" id="autres_actions_urgence" name="autres_actions_urgence" placeholder="Autres actions d'urgence" value="<?= htmlspecialchars(valeursAutres($actionsUrgenceSauvees, $actionsUrgenceConnues)) ?>">
                </div>
                <div>
                    <label><strong>Acteurs mobilisés :</strong></label>
                    <?php foreach ($acteursConnus as $val): ?>
                        <label><input type="checkbox" name="acteurs_mobilises[]" value="<?= htmlspecialchars($val) ?>" <?= coche($val, $acteursSauves) ?>> <?= htmlspecialchars($val) ?></label>
                    <?php endforeach; ?>
                    <input type="text" id="autres_acteurs_mobilises" name="autres_acteurs_mobilises" placeholder="Autres acteurs mobilisés" value="<?= htmlspecialchars(valeursAutres($acteursSauves, $acteursConnus)) ?>">
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Moyens matériels déployés</label>
                    <input type="text" name="moyens_materiels" placeholder="Ex: Motopompes, engins, embarcations..." value="<?= htmlspecialchars($bilan['moyens_materiels'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Difficultés rencontrées pendant la crise</label>
                    <input type="text" name="difficultes_intervention" value="<?= htmlspecialchars($bilan['difficultes_intervention'] ?? '') ?>">
                </div>
            </div>
        </fieldset>

        <!-- ÉTAPE 6 : ACTIONS POST-CRISE (APRÈS LA DÉCRUE) -->
        <fieldset>
            <legend>6. Actions post-crise (après la crise)</legend>
            <div class="checkbox-group">
                <?php foreach ($actionsPostCriseConnues as $val): ?>
                    <label><input type="checkbox" name="actions_post_crise[]" value="<?= htmlspecialchars($val) ?>" <?= coche($val, $actionsPostCriseSauvees) ?>> <?= htmlspecialchars($val) ?></label>
                <?php endforeach; ?>
                <input type="text" id="autres_actions_post_crise" name="autres_actions_post_crise" placeholder="Autres actions post-crise" value="<?= htmlspecialchars(valeursAutres($actionsPostCriseSauvees, $actionsPostCriseConnues)) ?>">
            </div>
            <div class="form-group">
                <label>État d'avancement des actions post-crise</label>
                <select name="avancement_post_crise">
                    <?php foreach (['Non engagées', 'En cours', 'Partiellement réalisées', 'Terminées'] as $av): ?>
                        <option value="<?= htmlspecialchars($av) ?>" <?= $bilan['avancement_post_crise'] === $av ? 'selected' : '' ?>><?= htmlspecialchars($av) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </fieldset>

        <!-- ÉTAPE 7 : ACTIONS À PRENDRE POUR ÉVITER LES RISQUES FUTURS -->
        <fieldset>
            <legend>7. Actions à prendre pour éviter les risques futurs</legend>
            <div class="grid-2 checkbox-group">
                <div>
                    <label><strong>Mesures structurelles :</strong></label>
                    <?php foreach ($mesuresStructurellesConnues as $val): ?>
                        <label><input type="checkbox" name="mesures_structurelles[]" value="<?= htmlspecialchars($val) ?>" <?= coche($val, $mesuresStructurellesSauvees) ?>> <?= htmlspecialchars($val) ?></label>
                    <?php endforeach; ?>
                    <input type="text" id="autres_mesures_structurelles" name="autres_mesures_structurelles" placeholder="Autres mesures structurelles" value="<?= htmlspecialchars(valeursAutres($mesuresStructurellesSauvees, $mesuresStructurellesConnues)) ?>">
                </div>
                <div>
                    <label><strong>Mesures non structurelles :</strong></label>
                    <?php foreach ($mesuresNonStructurellesConnues as $val): ?>
                        <label><input type="checkbox" name="mesures_non_structurelles[]" value="<?= htmlspecialchars($val) ?>" <?= coche($val, $mesuresNonStructurellesSauvees) ?>> <?= htmlspecialchars($val) ?></label>
                    <?php endforeach; ?>
                    <input type="text" id="autres_mesures_non_structurelles" name="autres_mesures_non_structurelles" placeholder="Autres mesures non structurelles" value="<?= htmlspecialchars(valeursAutres($mesuresNonStructurellesSauvees, $mesuresNonStructurellesConnues)) ?>">
                </div>
            </div>
            <div class="grid-3">
                <div class="form-group">
                    <label>Action prioritaire</label>
                    <input type="text" name="action_prioritaire" value="<?= htmlspecialchars($bilan['action_prioritaire'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Responsable désigné</label>
                    <input type="text" name="responsable_action" value="<?= htmlspecialchars($bilan['responsable_action'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Horizon de mise en œuvre</label>
                    <select name="horizon_action">
                        <?php foreach (['Court terme (< 3 mois)' => 'Court terme (&lt; 3 mois)', 'Moyen terme (3-12 mois)' => 'Moyen terme (3–12 mois)', 'Long terme (> 1 an)' => 'Long terme (&gt; 1 an)'] as $valeur => $libelle): ?>
                            <option value="<?= htmlspecialchars($valeur) ?>" <?= $bilan['horizon_action'] === $valeur ? 'selected' : '' ?>><?= $libelle ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <button type="submit">Enregistrer les modifications</button>
    </form>
</div>
</div>
    </section>
  </div><!-- /.content-wrapper -->

</div><!-- /.wrapper -->

<script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="../dist/js/app.min.js"></script>

<script>
// Affiche ou masque le champ texte libre lorsque "Autre commune..." est choisie
function toggleAutreCommune() {
    const select = document.getElementById('commune');
    const blocAutre = document.getElementById('bloc-commune-autre');
    const inputAutre = document.getElementById('commune_autre');
    const estAutre = (select.value === 'Autre');

    blocAutre.style.display = estAutre ? 'block' : 'none';
    inputAutre.required = estAutre;
}

// Coordonnées existantes du bilan (point de départ de la carte, pas de géolocalisation auto)
const latInit = <?= (float)$bilan['latitude'] ?>;
const lngInit = <?= (float)$bilan['longitude'] ?>;

const latInput = document.getElementById('latitude');
const lngInput = document.getElementById('longitude');
const statutGps = document.getElementById('statut-gps');

// Initialisation de la carte Leaflet centrée sur la position déjà enregistrée
const pickerMap = L.map('map-picker').setView([latInit, lngInit], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
}).addTo(pickerMap);

// Création du marqueur déplaçable (draggable: true)
const marker = L.marker([latInit, lngInit], { draggable: true }).addTo(pickerMap);

// Met à jour les champs latitude/longitude et la position du marqueur
function mettreAJourCoordonnees(lat, lng, centrer = false) {
    const latFixe = Number(lat).toFixed(6);
    const lngFixe = Number(lng).toFixed(6);
    latInput.value = latFixe;
    lngInput.value = lngFixe;
    marker.setLatLng([latFixe, lngFixe]);
    if (centrer) {
        pickerMap.setView([latFixe, lngFixe], 15);
    }
}

// Mise à jour quand l'utilisateur fait glisser le marqueur
marker.on('dragend', function () {
    const pos = marker.getLatLng();
    mettreAJourCoordonnees(pos.lat, pos.lng, false);
    statutGps.textContent = 'Coordonnées mises à jour par déplacement du marqueur.';
});

// Mise à jour quand l'utilisateur clique directement sur la carte
pickerMap.on('click', function (e) {
    mettreAJourCoordonnees(e.latlng.lat, e.latlng.lng, false);
    statutGps.textContent = 'Coordonnées mises à jour par clic sur la carte.';
});

// Synchronisation si les coordonnées sont saisies à la main dans les champs
function syncDepuisChamps() {
    const lat = parseFloat(latInput.value);
    const lng = parseFloat(lngInput.value);
    if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
        mettreAJourCoordonnees(lat, lng, true);
    }
}
latInput.addEventListener('change', syncDepuisChamps);
lngInput.addEventListener('change', syncDepuisChamps);

// Géolocalisation automatique via le navigateur (uniquement sur demande explicite en modification)
function localiserAutomatiquement(silencieux = false) {
    if (!navigator.geolocation) {
        if (!silencieux) statutGps.textContent = 'Géolocalisation non supportée par ce navigateur.';
        return;
    }
    if (!silencieux) statutGps.textContent = 'Détection de la position GPS en cours...';

    navigator.geolocation.getCurrentPosition(
        function (position) {
            mettreAJourCoordonnees(position.coords.latitude, position.coords.longitude, true);
            statutGps.textContent = 'Position GPS détectée (tu peux déplacer le marqueur si besoin).';
        },
        function () {
            statutGps.textContent = 'Position GPS indisponible : déplace le marqueur sur la carte ou saisis les coordonnées.';
        },
        { enableHighAccuracy: true, timeout: 7000 }
    );
}
</script>
</body>
</html>
