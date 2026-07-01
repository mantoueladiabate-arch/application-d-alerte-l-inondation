<?php
// Connexion à la base de données PostgreSQL
$host = 'localhost';
$dbname = 'base_inondation';
$user = 'postgres'; // Utilisateur PostgreSQL
$password = '0151516084'; // Mot de passe PostgreSQL

// Connexion PDO
try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION,
);
} catch (PDOException $e) {
    die("La connexion a échoué : " . $e->getMessage());
}

// Récupérer toutes les photos depuis la base de données
$stmt = $conn->prepare("SELECT * FROM photos ORDER BY date_ajout DESC");
$stmt->execute();
$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie des Photos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .photo-container {
            margin-top: 20px;
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center my-4">Galerie des Photos</h2>

    <div class="row photo-container">
        <?php foreach ($photos as $photo): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="photo/<?php echo htmlspecialchars($photo['chemin_image']); ?>" class="card-img-top" alt="Image">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($photo['titre']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($photo['description']); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


</body>
</html>