<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Historique des alertes</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <link rel="stylesheet" href="../bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="../dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap.min.css">

  <style>
    .media-thumbnail { max-width: 80px; max-height: 80px; }
  </style>
</head>

<body class="skin-blue sidebar-mini">
<div class="wrapper">

  <?php include __DIR__ . '/../includes/sidebar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><i class="fa fa-history"></i> Historique des alertes</h1>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Alertes signalées</h3>
              <div class="box-tools pull-right">
                <button id="reloadIncidentsBtn" class="btn btn-info btn-sm">
                  <i class="fa fa-refresh"></i> Recharger
                </button>
                <a href="../contact/index.php" class="btn btn-danger btn-sm">
                  <i class="fa fa-exclamation-triangle"></i> Émettre une alerte
                </a>
              </div>
            </div>
            <div class="box-body table-responsive">
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
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div><!-- /.content-wrapper -->

</div><!-- /.wrapper -->

<!-- Modale détails incident -->
<div class="modal fade" id="incidentDetailsModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Détails de l'incident #<span id="modalIncidentId"></span></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <p><strong>Commune :</strong> <span id="modalCommune"></span></p>
            <p><strong>Quartier :</strong> <span id="modalQuartier"></span></p>
            <p><strong>Type de risque :</strong> <span id="modalRisques"></span></p>
            <p><strong>Date :</strong> <span id="modalDate"></span></p>
            <p><strong>Localisation :</strong> <span id="modalLatLon"></span></p>
          </div>
          <div class="col-md-6">
            <p><strong>Description :</strong> <span id="modalDescription"></span></p>
            <p><strong>Recommandation :</strong> <span id="modalRecommandation"></span></p>
            <p><strong>Fichier joint :</strong></p>
            <div id="modalFichier"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap.min.js"></script>
<script src="../dist/js/app.min.js"></script>

<script>
$(document).ready(function() {
    var API_URL      = 'http://localhost/application-d-alerte-l-inondation/historiques/api_historiques.php';
    var BASE_FILE    = '/application-d-alerte-l-inondation/contact/informations/';

    var table = $('#incidentsTable').DataTable({
        language: { url: './i18n/French.json' },
        order: [[1, 'desc']],
        columns: [
            { data: 'id', width: '50px' },
            { data: 'date',
              render: function(data) {
                if (!data) return '—';
                return new Date(data).toLocaleDateString('fr-FR');
              }
            },
            { data: 'commune' },
            { data: 'quartier' },
            { data: 'risques' },
            { data: 'fichier', orderable: false, searchable: false,
              render: function(data) {
                if (!data) return '<span class="text-muted">Aucun</span>';
                var ext = data.split('.').pop().toLowerCase();
                return ['jpg','jpeg','png','gif','mp4','mov','webm'].indexOf(ext) >= 0
                       ? '<i class="fa fa-paperclip"></i> Fichier'
                       : '<i class="fa fa-file"></i> Fichier';
              }
            },
            { data: 'description',
              render: function(data) {
                if (!data) return '<em class="text-muted">—</em>';
                return data.length > 60 ? data.substr(0,60)+'…' : data;
              }
            },
            { data: 'recommandation',
              render: function(data) {
                if (!data) return '<em class="text-muted">Non renseigné</em>';
                return data.length > 60 ? data.substr(0,60)+'…' : data;
              }
            },
            { data: null, defaultContent: '<button class="btn btn-info btn-xs view-details"><i class="fa fa-eye"></i> Détails</button>',
              orderable: false, searchable: false }
        ]
    });

    function loadIncidents() {
        $.ajax({
            url: API_URL, method: 'GET', dataType: 'json',
            success: function(r) {
                if (r.success) {
                    table.clear().rows.add(r.data).draw();
                } else {
                    console.error('Erreur API:', r.message);
                }
            },
            error: function(x, s, e) { console.error('Erreur AJAX:', s, e); }
        });
    }

    loadIncidents();

    $('#reloadIncidentsBtn').on('click', function() { loadIncidents(); });

    $('#incidentsTable tbody').on('click', '.view-details', function() {
        var d = table.row($(this).parents('tr')).data();
        if (!d) return;

        $('#modalIncidentId').text(d.id);
        $('#modalCommune').text(d.commune || '—');
        $('#modalQuartier').text(d.quartier || '—');
        $('#modalRisques').text(d.risques || '—');
        $('#modalDate').text(d.date ? new Date(d.date).toLocaleDateString('fr-FR') : '—');
        $('#modalLatLon').text(d.latitude && d.longitude ? d.latitude + ', ' + d.longitude : 'N/A');
        $('#modalDescription').text(d.description || '—');
        $('#modalRecommandation').text(d.recommandation || 'Aucune recommandation.');

        var $f = $('#modalFichier').empty();
        if (d.fichier) {
            var path = BASE_FILE + d.fichier;
            var ext  = d.fichier.split('.').pop().toLowerCase();
            if (['jpg','jpeg','png','gif'].indexOf(ext) >= 0) {
                $f.append('<img src="'+path+'" class="img-responsive" alt="Fichier joint">');
            } else if (['mp4','mov','webm'].indexOf(ext) >= 0) {
                $f.append('<video src="'+path+'" class="img-responsive" controls></video>');
            } else {
                $f.append('<a href="'+path+'" target="_blank" class="btn btn-primary btn-sm">Ouvrir le document</a>');
            }
        } else {
            $f.append('<span class="text-muted">Aucun fichier joint.</span>');
        }

        $('#incidentDetailsModal').modal('show');
    });
});
</script>
</body>
</html>
