<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<?php
// ============================================================================
// ÉTAPE 1 : CONNEXION À LA BASE POSTGRESQL ET CHARGEMENT DES BILANS
// ============================================================================
require_once __DIR__ . '/../config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . htmlspecialchars($e->getMessage()));
}

// Suppression d'un bilan (bouton corbeille sur chaque carte)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM bilans WHERE id_bilan = :id");
    $stmt->execute([':id' => (int)$_POST['delete_id']]);
    header('Location: index.php?deleted=1');
    exit;
}

// Récupération de tous les bilans depuis la table `bilans` (du plus récent au plus ancien)
$stmt = $pdo->query("SELECT * FROM bilans ORDER BY id_bilan DESC");
$bilans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Paramètres d'URL transmis après l'enregistrement/la suppression d'un bilan
$estCree        = isset($_GET['created']) && $_GET['created'] === '1';
$idSurbrillance = isset($_GET['highlight']) ? (int)$_GET['highlight'] : 0;
$estSupprime    = isset($_GET['deleted']) && $_GET['deleted'] === '1';
$estModifie     = isset($_GET['updated']) && $_GET['updated'] === '1';

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

// ============================================================================
// ÉTAPE 2 : CALCUL DES INDICATEURS GLOBAUX (KPI)
// ============================================================================
$totalBilans      = count($bilans);
$totalSinistres   = array_sum(array_map(fn($b) => (int)$b['nb_menages_sinistres'], $bilans));
$totalHabitations = array_sum(array_map(fn($b) => (int)$b['nb_habitations_touchees'], $bilans));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilan</title>

    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">

    <!-- Bibliothèque cartographique Leaflet pour afficher les bilans géolocalisés -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #fiche-bilan-page { font-family: system-ui, -apple-system, sans-serif; background: #f3f5f8; color: #1e293b; margin: 0; padding: 24px; }
        #fiche-bilan-page .page { max-width: 980px; margin: 0 auto; }
        #fiche-bilan-page .topbar { display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        #fiche-bilan-page h1 { margin: 0; font-size: 1.45rem; color: #0f4c81; }
        #fiche-bilan-page .meta { color: #64748b; font-size: 0.9rem; }
        #fiche-bilan-page .actions-top { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        #fiche-bilan-page select { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; }
        #fiche-bilan-page .btn-lien { text-decoration: none; background: #0f4c81; color: #fff; padding: 8px 14px; border-radius: 6px; font-weight: 600; font-size: 0.92rem; }
        #fiche-bilan-page .btn-lien:hover { background: #09355b; }
        #fiche-bilan-page .alert-success { background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-weight: 600; border: 1px solid #86efac; }
        #fiche-bilan-page .kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px; }
        #fiche-bilan-page .kpi { background: #fff; padding: 14px 16px; border-radius: 8px; border: 1px solid #e2e8f0; }
        #fiche-bilan-page .kpi-label { font-size: 0.8rem; color: #64748b; }
        #fiche-bilan-page .kpi-val { font-size: 1.4rem; font-weight: 700; color: #0f4c81; margin-top: 4px; }
        #fiche-bilan-page #carte-bilans { height: 280px; border-radius: 8px; border: 1px solid #cbd5e1; margin-bottom: 20px; background: #e2e8f0; }
        #fiche-bilan-page .carte { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px 20px; margin-bottom: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        #fiche-bilan-page .carte.highlight { border: 2px solid #0f4c81; background: #f8fbff; }
        #fiche-bilan-page .carte-top { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        #fiche-bilan-page .titre-lieu { font-weight: 700; font-size: 1.05rem; }
        #fiche-bilan-page .badge { padding: 3px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 600; }
        #fiche-bilan-page .badge-consolide { background: #dcfce7; color: #166534; }
        #fiche-bilan-page .badge-partiel { background: #fef3c7; color: #92400e; }
        #fiche-bilan-page .badge-brouillon { background: #e2e8f0; color: #334155; }
        #fiche-bilan-page .ligne { margin: 6px 0; font-size: 0.95rem; }
        #fiche-bilan-page .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
        #fiche-bilan-page button, #fiche-bilan-page .actions a { padding: 7px 14px; border-radius: 6px; border: 1px solid #0f4c81; background: #fff; color: #0f4c81; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 0.92rem; }
        #fiche-bilan-page button.primaire { background: #0f4c81; color: #fff; }
        #fiche-bilan-page .actions form { margin: 0; }
        #fiche-bilan-page button.danger, #fiche-bilan-page .actions a.danger { border-color: #dc2626; color: #dc2626; }
        #fiche-bilan-page .actions a.telecharger { border-color: #166534; color: #166534; }
        #fiche-bilan-page .details { display: none; margin-top: 14px; padding-top: 12px; border-top: 1px dashed #cbd5e1; font-size: 0.92rem; color: #334155; }
        #fiche-bilan-page .vide { background: #fff; padding: 24px; border-radius: 8px; text-align: center; color: #64748b; border: 1px solid #e2e8f0; }
        @media (max-width: 650px) { #fiche-bilan-page .kpis { grid-template-columns: 1fr; } }
    </style>
</head>

<body class="skin-blue sidebar-mini">
<div class="wrapper">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><i class="fa fa-file-text-o"></i> Bilans</h1>
    </section>

    <section class="content">
<div id="fiche-bilan-page">
<div class="page">
    <!-- ÉTAPE 3 : EN-TÊTE, FILTRE PAR STATUT ET BOUTON VERS LE FORMULAIRE -->
    <div class="topbar">
        <div>
            <h1>Bilans</h1>
            <div class="meta">Actualisé automatiquement dès la validation du formulaire</div>
        </div>
        <div class="actions-top">
            <select id="filtreStatut" onchange="filtrerBilans()">
                <option value="ALL">Tous les statuts</option>
                <option value="Consolidé">Consolidé</option>
                <option value="Partiel">Partiel</option>
                <option value="Brouillon">Brouillon</option>
            </select>
            <a class="btn-lien" href="formulaire_bilan.php">+ Nouveau bilan</a>
        </div>
    </div>

    <!-- MESSAGE DE CONFIRMATION APRÈS ENREGISTREMENT -->
    <?php if ($estCree): ?>
        <div class="alert-success">
            Le bilan #BIL-<?= $idSurbrillance ?> a été enregistré et apparaît en tête de la liste ci-dessous.
        </div>
    <?php endif; ?>
    <?php if ($estSupprime): ?>
        <div class="alert-success">
            Le bilan a été supprimé.
        </div>
    <?php endif; ?>
    <?php if ($estModifie): ?>
        <div class="alert-success">
            Le bilan #BIL-<?= $idSurbrillance ?> a été modifié.
        </div>
    <?php endif; ?>

    <!-- ÉTAPE 4 : INDICATEURS SYNTHÉTIQUES (KPI) -->
    <div class="kpis">
        <div class="kpi">
            <div class="kpi-label">Bilans affichés</div>
            <div class="kpi-val" id="kpiNb"><?= $totalBilans ?></div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Ménages sinistrés</div>
            <div class="kpi-val" id="kpiSinistres"><?= $totalSinistres ?></div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Habitations touchées</div>
            <div class="kpi-val" id="kpiHabit"><?= $totalHabitations ?></div>
        </div>
    </div>

    <!-- ÉTAPE 5 : CARTE LEAFLET DES BILANS -->
    <div id="carte-bilans"></div>

    <!-- ÉTAPE 6 : LISTE DES CARTES DE BILAN -->
    <div id="liste-bilans">
        <?php if (empty($bilans)): ?>
            <div class="vide">
                Aucun bilan enregistré pour le moment. Clique sur <strong>+ Nouveau bilan</strong> pour en rédiger un.
            </div>
        <?php else: ?>
            <?php foreach ($bilans as $bilan): ?>
                <?php
                    $id            = (int)$bilan['id_bilan'];
                    $statut        = $bilan['statut_bilan'] ?? 'Brouillon';
                    $badgeClass    = match ($statut) {
                        'Consolidé' => 'badge-consolide',
                        'Partiel'   => 'badge-partiel',
                        default     => 'badge-brouillon',
                    };
                    $causesTxt     = trim(
                        formaterListePg($bilan['causes_meteo'] ?? null) . ', ' .
                        formaterListePg($bilan['causes_urbanisme_drainage'] ?? null),
                        ', '
                    );
                    $estMisEnAvant = ($id === $idSurbrillance);
                    $dateAffichage = !empty($bilan['date_debut'])
                        ? date('d/m/Y H:i', strtotime($bilan['date_debut']))
                        : 'Date non renseignée';
                ?>
                <article
                    id="bilan-<?= $id ?>"
                    class="carte <?= $estMisEnAvant ? 'highlight' : '' ?>"
                    data-statut="<?= htmlspecialchars($statut) ?>"
                    data-sinistres="<?= (int)$bilan['nb_menages_sinistres'] ?>"
                    data-habit="<?= (int)$bilan['nb_habitations_touchees'] ?>"
                    data-lat="<?= (float)$bilan['latitude'] ?>"
                    data-lng="<?= (float)$bilan['longitude'] ?>"
                    data-titre="<?= htmlspecialchars('#BIL-' . $id . ' — ' . $bilan['commune'] . ' (' . $bilan['quartier'] . ')') ?>"
                >
                    <div class="carte-top">
                        <div>
                            <span class="titre-lieu">#BIL-<?= $id ?> — <?= htmlspecialchars($bilan['commune']) ?> (<?= htmlspecialchars($bilan['quartier']) ?>)</span>
                            <span class="meta">• <?= htmlspecialchars($dateAffichage) ?></span>
                        </div>
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($statut) ?></span>
                    </div>

                    <div class="ligne"><strong>Causes :</strong> <?= htmlspecialchars($causesTxt) ?></div>
                    <div class="ligne">
                        <strong>Dégâts :</strong>
                        <?= (int)$bilan['nb_menages_sinistres'] ?> ménages sinistrés •
                        <?= (int)$bilan['nb_habitations_touchees'] ?> habitations touchées (<?= (int)$bilan['nb_habitations_detruites'] ?> détruites) •
                        <?= (int)$bilan['nb_deces'] ?> décès, <?= (int)$bilan['nb_blesses'] ?> blessés
                    </div>
                    <div class="ligne">
                        <strong>Action prioritaire :</strong>
                        <?= htmlspecialchars($bilan['action_prioritaire'] ?: 'Non précisée') ?>
                        <?php if (!empty($bilan['horizon_action'])): ?>
                            (<?= htmlspecialchars($bilan['horizon_action']) ?><?= !empty($bilan['responsable_action']) ? ' — ' . htmlspecialchars($bilan['responsable_action']) : '' ?>)
                        <?php endif; ?>
                    </div>

                    <div class="actions">
                        <button type="button" class="primaire" onclick="toggleDetails(<?= $id ?>)">Détails</button>
                        <button type="button" onclick="voirSurCarte(<?= (float)$bilan['latitude'] ?>, <?= (float)$bilan['longitude'] ?>)">Voir sur la carte</button>
                        <a href="modifier_bilan.php?id=<?= $id ?>" title="Modifier"><i class="fa fa-pencil"></i> Modifier</a>
                        <form method="POST" onsubmit="return confirm('Supprimer définitivement ce bilan ?');">
                            <input type="hidden" name="delete_id" value="<?= $id ?>">
                            <button type="submit" class="danger" title="Supprimer"><i class="fa fa-trash"></i> Supprimer</button>
                        </form>
                        <a class="telecharger" href="export_bilan.php?id=<?= $id ?>&format=word" title="Télécharger en Word"><i class="fa fa-file-word-o"></i> Word</a>
                        <a class="telecharger" href="export_bilan.php?id=<?= $id ?>&format=pdf" title="Télécharger en PDF"><i class="fa fa-file-pdf-o"></i> PDF</a>
                    </div>

                    <!-- DÉTAIL DÉPLIABLE DU BILAN -->
                    <div id="details-<?= $id ?>" class="details">
                        <p><strong>Localisation précise :</strong> <?= htmlspecialchars($bilan['repere_precis'] ?: 'Non précisé') ?> (<?= htmlspecialchars($bilan['type_milieu'] ?: 'Milieu non précisé') ?> — <?= (float)$bilan['latitude'] ?>, <?= (float)$bilan['longitude'] ?>)</p>
                        <p><strong>Analyse des causes :</strong> <?= htmlspecialchars($bilan['details_causes'] ?: 'Aucune précision supplémentaire') ?></p>
                        <p><strong>Infrastructures &amp; santé :</strong>
                            Infrastructures : <?= htmlspecialchars(formaterListePg($bilan['infrastructures_endommagees'] ?? null)) ?> |
                            Risques sanitaires : <?= htmlspecialchars(formaterListePg($bilan['risques_sanitaires'] ?? null)) ?> |
                            Activités : <?= htmlspecialchars(formaterListePg($bilan['activites_touchees'] ?? null)) ?>
                        </p>
                        <p><strong>Pertes &amp; besoins :</strong> <?= htmlspecialchars($bilan['estimation_pertes'] ?: 'Non renseigné') ?></p>
                        <p><strong>Actions pendant la crise :</strong> <?= htmlspecialchars(formaterListePg($bilan['actions_urgence'] ?? null)) ?> | Acteurs : <?= htmlspecialchars(formaterListePg($bilan['acteurs_mobilises'] ?? null)) ?></p>
                        <p><strong>Moyens &amp; difficultés :</strong> Moyens : <?= htmlspecialchars($bilan['moyens_materiels'] ?: 'Non précisé') ?> | Difficultés : <?= htmlspecialchars($bilan['difficultes_intervention'] ?: 'Aucune signalée') ?></p>
                        <p><strong>Actions post-crise :</strong> <?= htmlspecialchars(formaterListePg($bilan['actions_post_crise'] ?? null)) ?> — <em>Avancement : <?= htmlspecialchars($bilan['avancement_post_crise']) ?></em></p>
                        <p><strong>Mesures préventives :</strong>
                            Structurelles : <?= htmlspecialchars(formaterListePg($bilan['mesures_structurelles'] ?? null)) ?> |
                            Non structurelles : <?= htmlspecialchars(formaterListePg($bilan['mesures_non_structurelles'] ?? null)) ?>
                        </p>
                        <p><strong>Rédacteur :</strong> <?= htmlspecialchars($bilan['redacteur_nom']) ?> (<?= htmlspecialchars($bilan['redacteur_fonction'] ?: 'Fonction non précisée') ?> — <?= htmlspecialchars($bilan['organisme'] ?: 'Organisme non précisé') ?>)</p>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</div>
    </section>
  </div><!-- /.content-wrapper -->

</div><!-- /.wrapper -->

<script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="../dist/js/app.min.js"></script>

<script>
// ============================================================================
// ÉTAPE 7 : CARTE LEAFLET, DÉPLIAGE DES DÉTAILS ET FILTRAGE PAR STATUT
// ============================================================================
const map = L.map('carte-bilans').setView([5.3599, -3.9870], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

// Placement d'un marqueur par bilan
const marqueurs = [];
document.querySelectorAll('.carte').forEach(carte => {
    const lat   = parseFloat(carte.dataset.lat);
    const lng   = parseFloat(carte.dataset.lng);
    const titre = carte.dataset.titre;
    if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
        const marker = L.marker([lat, lng]).addTo(map).bindPopup(titre);
        marqueurs.push(marker);
    }
});

// Ajustement automatique du zoom pour englober tous les bilans
if (marqueurs.length > 0) {
    const groupe = L.featureGroup(marqueurs);
    map.fitBounds(groupe.getBounds().pad(0.15));
}

function voirSurCarte(lat, lng) {
    map.setView([lat, lng], 15);
    document.getElementById('carte-bilans').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function toggleDetails(id) {
    const bloc = document.getElementById('details-' + id);
    bloc.style.display = (bloc.style.display === 'none' || bloc.style.display === '') ? 'block' : 'none';
}

function filtrerBilans() {
    const statutChoisi = document.getElementById('filtreStatut').value;
    const cartes = document.querySelectorAll('.carte');
    let nb = 0, sinistres = 0, habit = 0;

    cartes.forEach(carte => {
        const visible = (statutChoisi === 'ALL' || carte.dataset.statut === statutChoisi);
        carte.style.display = visible ? 'block' : 'none';
        if (visible) {
            nb++;
            sinistres += Number(carte.dataset.sinistres || 0);
            habit += Number(carte.dataset.habit || 0);
        }
    });

    document.getElementById('kpiNb').textContent = nb;
    document.getElementById('kpiSinistres').textContent = sinistres;
    document.getElementById('kpiHabit').textContent = habit;
}
</script>
</body>
</html>
