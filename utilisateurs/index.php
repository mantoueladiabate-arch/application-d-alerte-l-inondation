<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrateur') {
    header('Location: ../index.php'); // Redirige vers une page d'accès refusé
    exit;
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Gestion des Utilisateurs</title>
  <!--liens de leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
		<script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-groupedlayercontrol/0.6.1/leaflet.groupedlayercontrol.min.js"></script>
		<!--script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script-->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">

    <style>
      /*enlever le style des elements par defaut*/
      *{
        padding: 0;
        margin: 0;
        box-sizing: border-box;
      }

    
      .navbar{
         /*hauteur*/
			   height:5vh;
        /*largueur de l'image*/
        width: 100%; 
        /*centrer le contenu*/
        align-items: center;
        justify-content: center;
          
		    }

      .txt{
        /*si jamais le texte depasse pas de retour à la ligne*/
        white-space: nowrap;
        /*police de l'ecriture*/
        font-size: 30px;
        /*animation du texte*/
        animation: scroll 10s linear infinite;      
      }

        /*animation du texte*/
      @keyframes scroll {
        /* 0% */ from {
        /*decalage du texte tout à gauche*/
        transform: translateX(-100%);
        margin-left:100%
       }
        100% {
         transform: translateX(0%); 
        }
      }

   
          body { 
            background-color: #f8f9fa; 
          }
        .container {
           margin-top: 30px; 
          }
        .card-header { 
          background-color: #007bff; color: white; 
        }
        .table th, .table td {
           vertical-align: middle; 
          }
        .btn-action { 
          margin-right: 5px;
         }
   #createAccountForm .form-group { 
    margin-bottom: 1rem; 
  } /* Ajustement pour le formulaire dans la modale */

    </style>


  </head>
  <body class="skin-blue fixed" data-spy="scroll" data-target="#scrollspy">
    <div>
      <header class="main-header">
        <!-- Logo -->
        <a href="../index.php" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>A</b>LT</span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><b>Acceuil</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
         <span class="txt">Enregistrer un Nouveau Contact</span> 
        </nav>
      </header>
    </div>
    
    <div class="container">
    <h1 class="text-center mb-4">Gestion des Utilisateurs</h1>
    <div class="text-right mb-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#createUserModal">Ajouter un nouvel utilisateur</button>
    </div>
    <div class="card">
        <div class="card-header">Liste des Utilisateurs</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Nom d'utilisateur</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Date Création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userListBody">
                        </tbody>
                </table>
            </div>
            <div id="listMessage" class="mt-3"></div>
        </div>
    </div>
</div>
<div class="modal" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">Créer un nouveau compte utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="createAccountForm">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="nom">Nom :</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="prenom">Prénom :</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>
                         <div class="form-group col-md-6">
                            <label for="username">Nom d'utilisateur :</label>
                           <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="contact1">Contact 1 :</label>
                            <input type="tel" class="form-control" id="contact1" name="contact1" pattern="[0-9]{8,15}" title="Ex: 07XXXXXXXX" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="mail">Email :</label>
                            <input type="email" class="form-control" id="mail" name="mail" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="mot_de_passe">Mot de passe :</label>
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="confirm_mot_de_passe">Confirmer le mot de passe :</label>
                            <input type="password" class="form-control" id="confirm_mot_de_passe" name="confirm_mot_de_passe" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="role">Rôle :</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="analyste">Analyste</option>
                            <option value="administrateur">Administrateur</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Créer le compte</button>
                    <div id="createMessage" class="mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal " id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Modifier l'Utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_nom">Nom :</label>
                        <input type="text" class="form-control" id="edit_nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_prenom">Prénom :</label>
                        <input type="text" class="form-control" id="edit_prenom" name="prenom" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_username">Nom d'utilisateur :</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_contact1">Contact 1 :</label>
                        <input type="tel" class="form-control" id="edit_contact1" name="contact1" pattern="[0-9]{8,15}" title="Ex: 07XXXXXXXX" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_mail">Email :</label>
                        <input type="email" class="form-control" id="edit_mail" name="mail" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_mot_de_passe">Nouveau Mot de passe (laisser vide si inchangé) :</label>
                        <input type="password" class="form-control" id="edit_mot_de_passe" name="mot_de_passe">
                    </div>
                    <div class="form-group">
                        <label for="edit_confirm_mot_de_passe">Confirmer le nouveau mot de passe :</label>
                        <input type="password" class="form-control" id="edit_confirm_mot_de_passe" name="confirm_mot_de_passe">
                    </div>
                    <div class="form-group">
                        <label for="edit_role">Rôle :</label>
                        <select class="form-control" id="edit_role" name="role" required>
                            <option value="analyste">Analyste</option>
                            <option value="administrateur">Administrateur</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Enregistrer les modifications</button>
                    <div id="editMessage" class="mt-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
      // Essayez un chemin absolu si votre API est dans le même dossier
   const API_URL = 'api_utilisateurs.php';
  

    
function showMessage(elementId, message, type) {
    const element = document.getElementById(elementId);
    element.style.display = 'block';
    element.className = `alert alert-${type}`;
    element.textContent = message;
    setTimeout(() => {
        element.style.display = 'none';
    }, 3000);
}

    async function loadUsers() {
    const userListBody = document.getElementById('userListBody');
    userListBody.innerHTML = '<tr><td colspan="8" class="text-center">Chargement des utilisateurs...</td></tr>';
    document.getElementById('listMessage').style.display = 'none';

        try {
            console.log('Tentative de requête vers:', API_URL); // Ajout pour débogage
            const response = await fetch(API_URL);
          //  const data = await response.json();
          // Affichez le statut et les headers de la réponse
        console.log('Status:', response.status);
        console.log('Headers:', [...response.headers.entries()]);
 if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
         const text = await response.text(); // D'abord récupérez comme texte
        console.log('Réponse brute:', text);
          const data = JSON.parse(text); // Parser manuellement
        console.log('Données parsées:', data);
        //const data = await response.json();
       // console.log('Réponse du serveur:', data); // Ajout pour débogage

        if (data.success && data.data.length > 0) {
            if (data.success && data.data.length > 0) {
                userListBody.innerHTML = '';
                data.data.forEach(user => {
                    const row = userListBody.insertRow();
                    row.innerHTML = `
                        <td>${user.id}</td>
                        <td>${user.nom}</td>
                        <td>${user.prenom}</td>
                        <td>${user.username}</td>
                        <td>${user.contact1}</td>
                        <td>${user.mail}</td>
                        <td>${user.role}</td>
                        <td>${new Date(user.date_creation).toLocaleDateString('fr-FR')}</td>
                        <td>
                            <button class="btn btn-warning btn-sm btn-action edit-btn" data-id="${user.id}">Modifier</button>
                            <button class="btn btn-danger btn-sm btn-action delete-btn" data-id="${user.id}">Supprimer</button>
                        </td>
                    `;
                });
            } else if (data.success && data.data.length === 0) {
                userListBody.innerHTML = '<tr><td colspan="8" class="text-center">Aucun utilisateur enregistré pour le moment.</td></tr>';
            } else {
                userListBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Erreur lors du chargement: ${data.message || 'Erreur inconnue'}</td></tr>`;
                showMessage('listMessage', `Erreur de chargement: ${data.message || 'Erreur inconnue'}`, 'danger');
            }
        } catch (error) {
        console.error('Erreur détaillée:', error);
        userListBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">
            Erreur: ${error.message}</td></tr>`;
        showMessage('listMessage', `Erreur: ${error.message}`, 'danger');
        }
    }
 }
    // --- Gestion de la soumission du formulaire de création (dans la modale) ---
    document.getElementById('createAccountForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        showMessage('createMessage', 'Création du compte en cours...', 'info');

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();
 console.log("Réponse de l'API (POST) :", result); // AFFICHEZ LA RÉPONSE ICI
            if (result.success) {
                showMessage('createMessage', result.message, 'success');
                form.reset();
                $('#createUserModal').modal('hide'); // Ferme la modale de création
                loadUsers();
            } else {
                showMessage('createMessage', result.message, 'danger');
            }
        } catch (error) {
            console.error('Erreur réseau ou du serveur:', error);
            showMessage('createMessage', 'Erreur de communication avec le serveur.', 'danger');
        }
    });

    // --- Gestion de l'ouverture de la modale d'édition (bouton "Modifier") ---
    document.getElementById('userListBody').addEventListener('click', async function(event) {
        if (event.target.classList.contains('edit-btn')) {
            const userId = event.target.dataset.id;
            try {
                const response = await fetch(`${API_URL}?id=${userId}`);
                const data = await response.json();

                if (data.success && data.data) {
                    const user = data.data;
                    document.getElementById('edit_id').value = user.id;
                    document.getElementById('edit_nom').value = user.nom;
                    document.getElementById('edit_prenom').value = user.prenom;
                     document.getElementById('edit_username').value = user.username;
                    document.getElementById('edit_contact1').value = user.contact1;
                    document.getElementById('edit_mail').value = user.mail;
                    document.getElementById('edit_role').value = user.role;
                    document.getElementById('edit_mot_de_passe').value = '';
                    document.getElementById('edit_confirm_mot_de_passe').value = '';
                    $('#editUserModal').modal('show');
                    document.getElementById('editMessage').style.display = 'none';
                } else {
                    showMessage('listMessage', `Erreur: ${data.message || 'Utilisateur introuvable.'}`, 'danger');
                }
            } catch (error) {
                console.error('Erreur lors de la récupération de l\'utilisateur pour édition:', error);
                showMessage('listMessage', 'Erreur de communication pour l\'édition.', 'danger');
            }
        }
    });

    // --- Gestion de la soumission du formulaire d'édition (dans la modale) ---
    document.getElementById('editUserForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const userId = document.getElementById('edit_id').value;

        showMessage('editMessage', 'Mise à jour du compte en cours...', 'info');

        try {
            const response = await fetch(`${API_URL}?id=${userId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                showMessage('editMessage', result.message, 'success');
                $('#editUserModal').modal('hide');
                loadUsers();
            } else {
                showMessage('editMessage', result.message, 'danger');
            }
        } catch (error) {
            console.error('Erreur réseau ou du serveur lors de l\'édition:', error);
            showMessage('editMessage', 'Erreur de communication lors de l\'édition.', 'danger');
        }
    });

    // --- Gestion de la suppression d'un utilisateur ---
    document.getElementById('userListBody').addEventListener('click', async function(event) {
        if (event.target.classList.contains('delete-btn')) {
            const userId = event.target.dataset.id;
            if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
                showMessage('listMessage', 'Suppression en cours...', 'info');
                try {
                    const response = await fetch(`${API_URL}?id=${userId}`, {
                        method: 'DELETE'
                    });
                    const result = await response.json();

                    if (result.success) {
                        showMessage('listMessage', result.message, 'success');
                        loadUsers();
                    } else {
                        showMessage('listMessage', result.message, 'danger');
                    }
                } catch (error) {
                    console.error('Erreur réseau ou du serveur lors de la suppression:', error);
                    showMessage('listMessage', 'Erreur de communication lors de la suppression.', 'danger');
                }
            }
        }
    });

    // Charger les utilisateurs au chargement initial de la page
    document.addEventListener('DOMContentLoaded', loadUsers);
</script>
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script>  $.widget.bridge('uibutton', $.ui.button); </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
<!--script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script-->
  </body>
</html>