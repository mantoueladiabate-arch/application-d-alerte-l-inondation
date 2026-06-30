<!DOCTYPE html>
<html>
<head>
    <title>Test de Modale</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 50px; }
    </style>
</head>
<body>

    <h1>Test d'ouverture de Modale</h1>

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#incidentDetailsModal">
        Ouvrir la Modale
    </button>

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
            Contenu de la modale de test.
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
</body>
</html>