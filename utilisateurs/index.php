<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'administrateur'])) {
    header('Location: ../index.php?login=1');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Gestion des utilisateurs</title>
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
      <h1><i class="fa fa-users"></i> Gestion des utilisateurs</h1>
    </section>

    <section class="content">
      <div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Liste des utilisateurs</h3>
          <div class="box-tools pull-right">
            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#createUserModal">
              <i class="fa fa-plus"></i> Ajouter un utilisateur
            </button>
          </div>
        </div>
        <div class="box-body table-responsive">
          <div id="listMessage" class="mt-3" style="display:none;"></div>
          <table class="table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th>ID</th><th>Nom</th><th>Prénom</th><th>Nom d'utilisateur</th>
                <th>Contact</th><th>Email</th><th>Rôle</th><th>Date création</th><th>Actions</th>
              </tr>
            </thead>
            <tbody id="userListBody"></tbody>
          </table>
        </div>
      </div>
    </section>
  </div>

</div>

<!-- Modale création -->
<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Créer un nouveau compte</h4>
      </div>
      <div class="modal-body">
        <form id="createAccountForm">
          <div class="row">
            <div class="form-group col-md-6"><label>Nom</label><input type="text" class="form-control" name="nom" required></div>
            <div class="form-group col-md-6"><label>Prénom</label><input type="text" class="form-control" name="prenom" required></div>
            <div class="form-group col-md-6"><label>Nom d'utilisateur</label><input type="text" class="form-control" name="user_name" required></div>
            <div class="form-group col-md-6"><label>Contact 1</label><input type="tel" class="form-control" name="contact1" required></div>
            <div class="form-group col-md-6"><label>Email</label><input type="email" class="form-control" name="mail" required></div>
            <div class="form-group col-md-6"><label>Rôle</label>
              <select class="form-control" name="role" required>
                <option value="analyste">Analyste</option>
                <option value="administrateur">Administrateur</option>
              </select>
            </div>
            <div class="form-group col-md-6"><label>Mot de passe</label><input type="password" class="form-control" name="mot_de_passe" required></div>
            <div class="form-group col-md-6"><label>Confirmer le mot de passe</label><input type="password" class="form-control" name="confirm_mot_de_passe" required></div>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Créer le compte</button>
          <div id="createMessage" class="mt-3" style="display:none;"></div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modale édition -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Modifier l'utilisateur</h4>
      </div>
      <div class="modal-body">
        <form id="editUserForm">
          <input type="hidden" id="edit_id" name="id">
          <div class="form-group"><label>Nom</label><input type="text" class="form-control" id="edit_nom" name="nom" required></div>
          <div class="form-group"><label>Prénom</label><input type="text" class="form-control" id="edit_prenom" name="prenom" required></div>
          <div class="form-group"><label>Nom d'utilisateur</label><input type="text" class="form-control" id="edit_username" name="user_name" required></div>
          <div class="form-group"><label>Contact 1</label><input type="tel" class="form-control" id="edit_contact1" name="contact1" required></div>
          <div class="form-group"><label>Email</label><input type="email" class="form-control" id="edit_mail" name="mail" required></div>
          <div class="form-group"><label>Rôle</label>
            <select class="form-control" id="edit_role" name="role" required>
              <option value="analyste">Analyste</option>
              <option value="administrateur">Administrateur</option>
            </select>
          </div>
          <div class="form-group"><label>Nouveau mot de passe <small class="text-muted">(laisser vide si inchangé)</small></label><input type="password" class="form-control" id="edit_mot_de_passe" name="mot_de_passe"></div>
          <div class="form-group"><label>Confirmer le nouveau mot de passe</label><input type="password" class="form-control" id="edit_confirm_mot_de_passe" name="confirm_mot_de_passe"></div>
          <button type="submit" class="btn btn-success btn-block">Enregistrer les modifications</button>
          <div id="editMessage" class="mt-3" style="display:none;"></div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="../dist/js/app.min.js"></script>
<script>
  var API_URL = 'api_utilisateurs.php';

  function showMessage(id, msg, type) {
    var el = document.getElementById(id);
    el.style.display = 'block';
    el.className = 'alert alert-' + type;
    el.textContent = msg;
    setTimeout(function() { el.style.display = 'none'; }, 3000);
  }

  function loadUsers() {
    var body = document.getElementById('userListBody');
    body.innerHTML = '<tr><td colspan="9" class="text-center">Chargement...</td></tr>';
    fetch(API_URL)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success && data.data.length > 0) {
          body.innerHTML = '';
          data.data.forEach(function(u) {
            var row = body.insertRow();
            row.innerHTML =
              '<td>' + u.id + '</td>' +
              '<td>' + u.nom + '</td>' +
              '<td>' + u.prenom + '</td>' +
              '<td>' + u.user_name + '</td>' +
              '<td>' + u.contact1 + '</td>' +
              '<td>' + u.mail + '</td>' +
              '<td><span class="label label-' + (u.role === 'administrateur' ? 'danger' : 'info') + '">' + u.role + '</span></td>' +
              '<td>' + new Date(u.date_creation).toLocaleDateString('fr-FR') + '</td>' +
              '<td>' +
                '<button class="btn btn-warning btn-xs edit-btn" data-id="' + u.id + '"><i class="fa fa-edit"></i> Modifier</button> ' +
                '<button class="btn btn-danger btn-xs delete-btn" data-id="' + u.id + '"><i class="fa fa-trash"></i> Supprimer</button>' +
              '</td>';
          });
        } else if (data.success) {
          body.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Aucun utilisateur enregistré.</td></tr>';
        } else {
          body.innerHTML = '<tr><td colspan="9" class="text-center text-danger">' + (data.message || 'Erreur') + '</td></tr>';
        }
      })
      .catch(function(err) {
        body.innerHTML = '<tr><td colspan="9" class="text-center text-danger">Erreur: ' + err.message + '</td></tr>';
      });
  }

  document.getElementById('createAccountForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var data = Object.fromEntries(new FormData(this).entries());
    showMessage('createMessage', 'Création en cours...', 'info');
    fetch(API_URL, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(data) })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        showMessage('createMessage', res.message, res.success ? 'success' : 'danger');
        if (res.success) { document.getElementById('createAccountForm').reset(); $('#createUserModal').modal('hide'); loadUsers(); }
      })
      .catch(function() { showMessage('createMessage', 'Erreur de communication.', 'danger'); });
  });

  document.getElementById('userListBody').addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-btn') || e.target.closest('.edit-btn')) {
      var id = (e.target.closest('.edit-btn') || e.target).dataset.id;
      fetch(API_URL + '?id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(data) {
          if (data.success && data.data) {
            var u = data.data;
            document.getElementById('edit_id').value        = u.id;
            document.getElementById('edit_nom').value       = u.nom;
            document.getElementById('edit_prenom').value    = u.prenom;
            document.getElementById('edit_username').value  = u.user_name;
            document.getElementById('edit_contact1').value  = u.contact1;
            document.getElementById('edit_mail').value      = u.mail;
            document.getElementById('edit_role').value      = u.role;
            document.getElementById('edit_mot_de_passe').value = '';
            document.getElementById('edit_confirm_mot_de_passe').value = '';
            $('#editUserModal').modal('show');
          }
        });
    }
    if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
      var id = (e.target.closest('.delete-btn') || e.target).dataset.id;
      if (confirm('Supprimer cet utilisateur ? Cette action est irréversible.')) {
        fetch(API_URL + '?id=' + id, { method: 'DELETE' })
          .then(function(r) { return r.json(); })
          .then(function(res) { showMessage('listMessage', res.message, res.success ? 'success' : 'danger'); if (res.success) loadUsers(); });
      }
    }
  });

  document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var id   = document.getElementById('edit_id').value;
    var data = Object.fromEntries(new FormData(this).entries());
    showMessage('editMessage', 'Mise à jour en cours...', 'info');
    fetch(API_URL + '?id=' + id, { method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify(data) })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        showMessage('editMessage', res.message, res.success ? 'success' : 'danger');
        if (res.success) { $('#editUserModal').modal('hide'); loadUsers(); }
      })
      .catch(function() { showMessage('editMessage', 'Erreur de communication.', 'danger'); });
  });

  loadUsers();
</script>
</body>
</html>
