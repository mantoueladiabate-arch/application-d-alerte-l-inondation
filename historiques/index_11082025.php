<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Historique des incidents</title>

    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        /* Styles de base pour un affichage propre */
        body { background-color: #f8f9fa; }
        .container { margin-top: 30px; }
        .card-header { background-color: #007bff; color: white; }
        .table th, .table td { vertical-align: middle; }
        .detail-label { font-weight: bold; }
        .media-container { max-width: 100%; height: auto; display: block; margin-top: 15px; border: 1px solid #ddd; }
        /* Style pour les miniatures dans le tableau - si vous décidez de les garder */
        .media-thumbnail { max-width: 100px; max-height: 100px; }
    </style>
</head>
<body class="skin-blue fixed" data-spy="scroll" data-target="#scrollspy">
    
<div class="container">
    <h1 class="text-center mb-4">Historique des Incidents</h1>
    <div class="d-flex justify-content-between mb-3">
        <a href="../index.php" class="btn btn-info">Accueil</a>
        <a href="../contact/index.php" class="btn btn-danger">Signaler un nouvel incident</a>
        <button id="reloadIncidentsBtn" class="btn btn-info">Recharger la liste</button>
    </div>

    <div class="card">
        <div class="card-header">
            Historique des Incidents Signalés
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="incidentsTable" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date Événement</th>
                            <th>Commune</th>
                            <th>Quartier</th>
                            <th>Risque</th>
                            <th>Fichier</th>
                            <th>Description</th>
                            <th>Recommandation</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="incidentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="incidentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="incidentDetailsModalLabel">Détails de l'Incident #<span id="modalIncidentId"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Commune :</strong> <span id="modalCommune"></span></p>
                        <p><strong>Quartier :</strong> <span id="modalQuartier"></span></p>
                        <p><strong>Type de Risques :</strong> <span id="modalRisques"></span></p>
                        <p><strong>Date de l'Événement :</strong> <span id="modalDate"></span></p>
                        <p><strong>Date de Signalement :</strong> <span id="modalDateSignalisation"></span></p>
                        <p><strong>Dernière Mise à Jour :</strong> <span id="modalLastUpdated"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Localisation (Latitude, Longitude) :</strong> <span id="modalLatLon"></span></p>
                        <p><strong>Description :</strong> <span id="modalDescription"></span></p>
                        <p><strong>Recommandation / Actions :</strong> <span id="modalRecommandation"></span></p>
                        <p><strong>Fichier Joint :</strong></p>
                        <div id="modalFichier"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // URL de l'API pour récupérer les incidents
    const API_URL = 'http://localhost/plateforme_inondation/historiques/api_historiques.php';
    // Chemin de base pour accéder aux fichiers
    const BASE_FILE_PATH = 'http://localhost/plateforme_inondation/contact/informations/';

    // Initialisation de DataTables (sans données initiales)
    var table = $('#incidentsTable').DataTable({
        "language": {
            "url": "./i18n/French.json"
        },
        "columns": [
            { "data": "id" },
            { "data": "date" },
            { "data": "commune" },
            { "data": "quartier" },
            { "data": "risques" },
            // Affichage du fichier dans le tableau (CORRIGÉ)
            { "data": "fichier", "orderable": false, "searchable": false, "render": function(data, type, row) {
                if (!data) {
                    return 'Aucun';
                }
                const fileExt = data.split('.').pop().toLowerCase();
                // Affiche un texte ou une icône dans le tableau, sans afficher la miniature
                if (['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'webm'].includes(fileExt)) {
                    return `✔️ Fichier joint`;
                }
                return 'Fichier';
            }},
            { "data": "description", "render": function(data, type, row) {
                // Tronque la description pour l'affichage dans le tableau
                return data.length > 50 ? data.substr(0, 50) + '...' : data;
            }},
            { "data": "recommandation", "render": function(data, type, row) {
                return data ? (data.length > 50 ? data.substr(0, 50) + '...' : data) : 'Non renseigné';
            }},
            { "data": null, "defaultContent": '<button class="btn btn-info btn-sm view-details">Détails</button>', "orderable": false, "searchable": false }
        ]
    });

    // Fonction pour charger les données depuis l'API
    function loadIncidents() {
        $.ajax({
            url: API_URL,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Nettoie le tableau et ajoute les nouvelles données
                    table.clear().rows.add(response.data).draw();
                } else {
                    console.error('Erreur de l\'API:', response.message);
                    alert('Erreur lors du chargement des incidents : ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Erreur AJAX:', textStatus, errorThrown, jqXHR.responseText);
                alert('Impossible de charger les données. Vérifiez l\'URL de l\'API et le serveur.');
            }
        });
    }

    // Charge les incidents au chargement de la page
    loadIncidents();

    // Gestion du clic sur le bouton "Détails"
    $('#incidentsTable tbody').on('click', '.view-details', function() {
        var data = table.row($(this).parents('tr')).data();
        
        if (!data) {
            console.error("Erreur: Les données de la ligne sont introuvables.");
            return;
        }

        // Remplir la modale avec les données de l'incident
        $('#modalIncidentId').text(data.id);
        $('#modalCommune').text(data.commune);
        $('#modalQuartier').text(data.quartier);
        $('#modalRisques').text(data.risques);
        $('#modalDate').text(new Date(data.date).toLocaleDateString('fr-FR'));
        $('#modalDateSignalisation').text(new Date(data.date_signalisation).toLocaleString('fr-FR'));
        $('#modalLastUpdated').text(new Date(data.last_updated).toLocaleString('fr-FR'));
        $('#modalLatLon').text(`${data.latitude || 'N/A'}, ${data.longitude || 'N/A'}`);
        $('#modalDescription').text(data.description);
        $('#modalRecommandation').text(data.recommandation || 'Aucune recommandation.');

        // Afficher le fichier joint dans la modale (CORRIGÉ)
        const modalFichierDiv = $('#modalFichier');
        modalFichierDiv.empty(); // Nettoyer le contenu précédent
        
        if (data.fichier) {
            const fullFilePath = BASE_FILE_PATH + data.fichier;
            const fileExt = data.fichier.split('.').pop().toLowerCase();
            
            // Logique pour afficher le fichier en fonction de son type
            if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                modalFichierDiv.append(`<img src="${fullFilePath}" class="img-fluid" alt="Fichier joint">`);
            } else if (['mp4', 'mov', 'webm'].includes(fileExt)) {
                modalFichierDiv.append(`<video src="${fullFilePath}" class="img-fluid" controls></video>`);
            } else if (['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'].includes(fileExt)) {
                modalFichierDiv.append(`<a href="${fullFilePath}" target="_blank" class="btn btn-primary mt-2">Ouvrir le document</a>`);
            } else {
                modalFichierDiv.append(`<span>Fichier inconnu: <a href="${fullFilePath}" target="_blank">Télécharger</a></span>`);
            }
        } else {
            modalFichierDiv.append('<span>Aucun fichier joint.</span>');
        }

        // Afficher la modale
        $('#incidentDetailsModal').modal('show');
    });

    // Gestion du clic sur le bouton "Recharger"
    $('#reloadIncidentsBtn').on('click', function() {
        loadIncidents();
    });
});
</script>

</body>
</html>