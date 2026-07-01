<?php
$base    = '/application-d-alerte-l-inondation';
$current = $_SERVER['PHP_SELF'] ?? '';

function sidebarActive($current, $keyword) {
    return strpos($current, $keyword) !== false ? 'active' : '';
}
?>
<aside class="main-sidebar">
  <section class="sidebar">
    <div class="user-panel" style="padding:10px 15px 5px;">
      <div class="info">
        <a href="<?= $base ?>/index.php" style="color:#fff; font-weight:bold; font-size:15px;">
          <i class="fa fa-shield"></i> Alerte Inondation
        </a>
      </div>
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
