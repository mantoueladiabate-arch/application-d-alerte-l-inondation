<?php
// Chemin URL de la racine du projet, détecté automatiquement
$base = rtrim(str_replace(
    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
    '',
    str_replace('\\', '/', dirname(__DIR__))
), '/');
$current = $_SERVER['PHP_SELF'] ?? '';

function sidebarActive($current, $keyword) {
    return strpos($current, $keyword) !== false ? 'active' : '';
}
?>
<aside class="main-sidebar">
  <section class="sidebar">
    <div style="padding:12px 15px;overflow:visible;border-bottom:1px solid rgba(255,255,255,0.1);">
      <?php if (!empty($_SESSION['username'])): ?>
        <div style="display:flex;align-items:center;gap:10px;">
          <i class="fa fa-user-circle" style="font-size:32px;color:#aaa;flex-shrink:0;"></i>
          <div style="min-width:0;">
            <div style="color:#fff;font-weight:bold;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <?= htmlspecialchars($_SESSION['username']) ?>
            </div>
            <div style="font-size:11px;color:#aaa;margin-bottom:4px;">
              <?= htmlspecialchars($_SESSION['role'] ?? '') ?>
            </div>
            <a href="<?= $base ?>/logout.php" style="font-size:11px;color:#e74c3c;text-decoration:none;">
              <i class="fa fa-sign-out"></i> Se déconnecter
            </a>
          </div>
        </div>
      <?php else: ?>
        <a href="<?= $base ?>/index.php" style="color:#fff;font-weight:bold;font-size:14px;text-decoration:none;">
          <i class="fa fa-shield"></i> Alerte Inondation
        </a><br>
        <a href="<?= $base ?>/index.php?login=1" style="font-size:11px;color:#5bc0de;text-decoration:none;">
          <i class="fa fa-sign-in"></i> Se connecter
        </a>
      <?php endif; ?>
    </div>
    <ul class="sidebar-menu">

      <li class="treeview <?= (basename($current) === 'index.php' && !preg_match('#/(tableaubord|analyste|contact|historiques|documentation|ajout_contact|utilisateurs|statistique)/#', $current)) ? 'active' : '' ?>">
        <a href="<?= $base ?>/index.php"><i class="fa fa-home"></i><span>Accueil</span></a>
      </li>

      <li class="treeview <?= sidebarActive($current, 'tableaubord') ?>">
        <a href="<?= $base ?>/tableaubord/index.php"><i class="fa fa-table"></i><span>Tableau de bord</span></a>
      </li>

      <li class="treeview <?= sidebarActive($current, 'analyste') ?>">
        <a href="<?= $base ?>/analyste/index.php"><i class="fa fa-map-marker"></i><span>Analystes</span></a>
      </li>

      <li class="treeview <?= sidebarActive($current, 'statistique') ?>">
        <a href="<?= $base ?>/statistique/index.php"><i class="fa fa-bar-chart"></i><span>Statistique</span></a>
      </li>

      <li class="treeview <?= sidebarActive($current, 'contact') ?>">
        <a href="<?= $base ?>/contact/index.php"><i class="fa fa-exclamation-triangle"></i><span>Émettre une alerte</span></a>
      </li>

      <li class="treeview <?= sidebarActive($current, 'historiques') ?>">
        <a href="<?= $base ?>/historiques/index.php"><i class="fa fa-history"></i><span>Historiques</span></a>
      </li>

      <li class="treeview <?= sidebarActive($current, 'documentation') ?>">
        <a href="<?= $base ?>/documentation/index.php"><i class="fa fa-book"></i><span>Galerie</span></a>
      </li>

      <li class="treeview <?= (strpos($current, 'ajout_contact') !== false && strpos($current, 'liste') === false) ? 'active' : '' ?>">
        <a href="<?= $base ?>/ajout_contact/index.php"><i class="fa fa-user-plus"></i><span>Enregistrer un contact</span></a>
      </li>

      <li class="treeview <?= (strpos($current, 'ajout_contact') !== false && strpos($current, 'liste') !== false) ? 'active' : '' ?>">
        <a href="<?= $base ?>/ajout_contact/liste.php"><i class="fa fa-address-book"></i><span>Liste des contacts</span></a>
      </li>

      <li class="treeview <?= sidebarActive($current, 'utilisateurs') ?>">
        <a href="<?= $base ?>/utilisateurs/index.php"><i class="fa fa-user"></i><span>Gestion des utilisateurs</span></a>
      </li>

    </ul>
  </section>
</aside>
