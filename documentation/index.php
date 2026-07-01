<?php
// Récupère les images par dossier
function scanImages($dir, $webPath) {
    $exts = ['jpg','jpeg','png','gif','webp'];
    $images = [];
    if (!is_dir($dir)) return $images;
    foreach (scandir($dir) as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, $exts)) {
            $images[] = $webPath . '/' . rawurlencode($file);
        }
    }
    return $images;
}

$base    = __DIR__ . '/data';
$webBase = 'data';

$albums = [
    'Toutes'      => scanImages($base,         $webBase),
    '2019'        => scanImages($base . '/2019', $webBase . '/2019'),
    '2020'        => scanImages($base . '/2020', $webBase . '/2020'),
    'Anyama'      => array_values(array_filter(
                        scanImages($base, $webBase),
                        fn($f) => stripos($f, 'ANYAMA') !== false
                     )),
];
// Retirer les images Anyama du groupe "Toutes" pour éviter les doublons ? Non, on les garde toutes.
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Galerie — Alertes Inondation</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">
  <style>
    .gallery-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    .gallery-item {
      width: 180px;
      height: 140px;
      overflow: hidden;
      border-radius: 4px;
      cursor: pointer;
      border: 2px solid #ddd;
      transition: border-color .2s;
    }
    .gallery-item:hover { border-color: #3c8dbc; }
    .gallery-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    /* Lightbox */
    #lightbox {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.85);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }
    #lightbox.open { display: flex; }
    #lightbox img {
      max-width: 90vw;
      max-height: 85vh;
      border-radius: 4px;
      box-shadow: 0 0 30px rgba(0,0,0,.8);
    }
    #lightbox-close {
      position: absolute;
      top: 20px; right: 30px;
      font-size: 40px;
      color: #fff;
      cursor: pointer;
      line-height: 1;
    }
    #lightbox-prev, #lightbox-next {
      position: absolute;
      top: 50%; transform: translateY(-50%);
      font-size: 50px;
      color: #fff;
      cursor: pointer;
      user-select: none;
      padding: 10px;
    }
    #lightbox-prev { left: 15px; }
    #lightbox-next { right: 15px; }
    .album-count { font-size: 12px; color: #999; }
  </style>
</head>
<body class="skin-blue sidebar-mini">
<div class="wrapper">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><i class="fa fa-book"></i> Galerie</h1>
      <small>Photos des inondations à Abidjan</small>
    </section>

    <section class="content">

      <!-- Texte introductif -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">À propos</h3>
        </div>
        <div class="box-body">
          <p>Abidjan, la capitale économique de la Côte d'Ivoire, enregistre pendant les saisons pluvieuses plusieurs types d'inondations :
          remontée de la nappe phréatique, inondations dans les vallées et cuvettes, submersion marine et débordement des cours d'eau.</p>
          <p>Les facteurs humains — constructions sur les canalisations, urbanisation galopante — aggravent ces risques et entraînent des pertes en vies humaines et matérielles.</p>
        </div>
      </div>

      <!-- Onglets par année -->
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Photos</h3>
        </div>
        <div class="box-body">

          <ul class="nav nav-tabs" id="albumTabs">
            <?php $first = true; foreach ($albums as $label => $imgs): ?>
            <li class="<?= $first ? 'active' : '' ?>">
              <a href="#album-<?= $label ?>" data-toggle="tab">
                <?= htmlspecialchars($label) ?>
                <span class="badge"><?= count($imgs) ?></span>
              </a>
            </li>
            <?php $first = false; endforeach; ?>
          </ul>

          <div class="tab-content" style="padding-top:15px;">
            <?php $first = true; foreach ($albums as $label => $imgs): ?>
            <div class="tab-pane <?= $first ? 'active' : '' ?>" id="album-<?= $label ?>">
              <?php if (empty($imgs)): ?>
                <p class="text-muted">Aucune image dans cet album.</p>
              <?php else: ?>
              <div class="gallery-grid" data-album="<?= htmlspecialchars($label) ?>">
                <?php foreach ($imgs as $src): ?>
                <div class="gallery-item" onclick="openLightbox('<?= htmlspecialchars($src, ENT_QUOTES) ?>', this.closest('.gallery-grid'))">
                  <img src="<?= htmlspecialchars($src) ?>" alt="" loading="lazy">
                </div>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>
            <?php $first = false; endforeach; ?>
          </div>

        </div>
      </div>

    </section>
  </div><!-- /.content-wrapper -->

</div><!-- /.wrapper -->

<!-- Lightbox -->
<div id="lightbox">
  <span id="lightbox-close" onclick="closeLightbox()">&times;</span>
  <span id="lightbox-prev" onclick="lightboxNav(-1)">&#8249;</span>
  <img id="lightbox-img" src="" alt="">
  <span id="lightbox-next" onclick="lightboxNav(1)">&#8250;</span>
</div>

<script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="../dist/js/app.min.js"></script>
<script>
  var currentSrcs = [];
  var currentIndex = 0;

  function openLightbox(src, grid) {
    currentSrcs = Array.from(grid.querySelectorAll('.gallery-item img')).map(function(img) { return img.getAttribute('src'); });
    currentIndex = currentSrcs.indexOf(src);
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('open');
  }

  function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
  }

  function lightboxNav(dir) {
    currentIndex = (currentIndex + dir + currentSrcs.length) % currentSrcs.length;
    document.getElementById('lightbox-img').src = currentSrcs[currentIndex];
  }

  document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) closeLightbox();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') lightboxNav(-1);
    if (e.key === 'ArrowRight') lightboxNav(1);
  });
</script>
</body>
</html>
