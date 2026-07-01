<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';

// Traitement POST (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données.']);
        exit();
    }

    $nom      = $_POST['nom']      ?? '';
    $prenom   = $_POST['prenom']   ?? '';
    $contact1 = $_POST['contact1'] ?? '';
    $whatsapp = $_POST['whatsapp'] ?: null;
    $mail     = $_POST['mail']     ?: null;

    if (empty($nom) || empty($prenom) || empty($contact1)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires (Nom, Prénom, Contact 1).']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO contacts (nom, prenom, contact1, whatsapp, mail) VALUES (:nom, :prenom, :contact1, :whatsapp, :mail)");
        $stmt->execute([':nom' => $nom, ':prenom' => $prenom, ':contact1' => $contact1, ':whatsapp' => $whatsapp, ':mail' => $mail]);
        echo json_encode(['success' => true, 'message' => 'Contact enregistré avec succès !']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du contact.']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Enregistrer un contact</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">
</head>
<body class="skin-blue sidebar-mini">
<div class="wrapper">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><i class="fa fa-user-plus"></i> Enregistrer un contact</h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-6 col-md-offset-3">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Nouveau contact</h3>
              <div class="box-tools pull-right">
                <a href="liste.php" class="btn btn-default btn-sm"><i class="fa fa-list"></i> Liste des contacts</a>
              </div>
            </div>
            <div class="box-body">
              <form id="contactForm">
                <div class="form-group">
                  <label>Nom <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="nom" required>
                </div>
                <div class="form-group">
                  <label>Prénom <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="prenom" required>
                </div>
                <div class="form-group">
                  <label>Contact 1 <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control" name="contact1" placeholder="07XXXXXXXX ou +225XXXXXXXX" required>
                </div>
                <div class="form-group">
                  <label>Numéro WhatsApp <small class="text-muted">(optionnel)</small></label>
                  <input type="tel" class="form-control" name="whatsapp" placeholder="07XXXXXXXX">
                </div>
                <div class="form-group">
                  <label>Email <small class="text-muted">(optionnel)</small></label>
                  <input type="email" class="form-control" name="mail">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Enregistrer</button>
              </form>
              <div id="responseMessage" class="mt-3" style="display:none;"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

</div>

<script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="../dist/js/app.min.js"></script>
<script>
  document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var msg = document.getElementById('responseMessage');
    msg.style.display = 'block';
    msg.className = 'alert alert-info';
    msg.textContent = 'Envoi en cours...';

    fetch(window.location.href, { method: 'POST', body: formData })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        msg.className = data.success ? 'alert alert-success' : 'alert alert-danger';
        msg.textContent = data.message;
        if (data.success) document.getElementById('contactForm').reset();
        setTimeout(function() { msg.style.display = 'none'; }, 3000);
      })
      .catch(function() {
        msg.className = 'alert alert-danger';
        msg.textContent = 'Erreur de communication avec le serveur.';
        setTimeout(function() { msg.style.display = 'none'; }, 3000);
      });
  });
</script>
</body>
</html>
