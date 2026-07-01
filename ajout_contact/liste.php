<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = :id");
    $stmt->execute([':id' => (int)$_POST['delete_id']]);
    header('Location: liste.php?msg=supprime');
    exit;
}

$contacts = $pdo->query("SELECT * FROM contacts ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$total = count($contacts);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Liste des contacts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">
    <style>
        .content-wrapper { padding: 20px; }
        .table th { background-color: #3c8dbc; color: #fff; }
    </style>
</head>
<body class="skin-blue sidebar-mini">
<div class="wrapper">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><i class="fa fa-address-book"></i> Liste des contacts <small><?= $total ?> enregistré(s)</small></h1>
    </section>
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'supprime'): ?>
            <div class="alert alert-success">Contact supprimé.</div>
        <?php endif; ?>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Contacts enregistrés</h3>
                <div class="box-tools pull-right">
                    <a href="index.php" class="btn btn-success btn-sm">
                        <i class="fa fa-plus"></i> Ajouter un contact
                    </a>
                </div>
            </div>
            <div class="box-body table-responsive">
                <?php if ($total === 0): ?>
                    <p class="text-center text-muted">Aucun contact enregistré.</p>
                <?php else: ?>
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Contact 1</th>
                            <th>WhatsApp</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $c): ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td><?php echo htmlspecialchars($c['nom']); ?></td>
                            <td><?php echo htmlspecialchars($c['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($c['contact1']); ?></td>
                            <td><?php echo htmlspecialchars($c['whatsapp'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($c['mail'] ?? '—'); ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Supprimer ce contact ?')">
                                    <input type="hidden" name="delete_id" value="<?php echo $c['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-xs">
                                        <i class="fa fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
