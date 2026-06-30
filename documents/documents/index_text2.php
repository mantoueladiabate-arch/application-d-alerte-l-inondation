<?php

// Connexion à la base de données PostgreSQL
$host = 'localhost';
$db = 'base_inondation';  // Remplacez par votre nom de base de données
$user = 'postgres';   // Remplacez par votre nom d'utilisateur
$pass = 'postgres';  // Remplacez par votre mot de passe



try {
 // Connexion à PostgreSQL avec PDO
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


// Récupération des articles depuis la base de données
$query = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC");
$articles = $query->fetchAll(PDO::FETCH_ASSOC);

// Téléchargement d'un article
if (isset($_GET['download_id'])) {
    $id = $_GET['download_id'];
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($article) {
        // Téléchargement du contenu de l'article
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $article['title'] . '.txt"');
        echo "Titre: " . $article['title'] . "\n";
        echo "Contenu:\n" . $article['content'];
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Articles collectés </title>
    
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 50%;
        margin: 0 auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: center;
        color: #333;
    }

    form {
        display: flex;
        flex-direction: column;
    }

    label {
        font-size: 1rem;
        margin-bottom: 8px;
    }

    input, textarea {
        padding: 10px;
        font-size: 1rem;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    textarea {
        resize: vertical;
    }

    button {
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    button:hover {
        background-color: #45a049;
    }

    .articles {
        margin-top: 40px;
    }

    .article {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
    }

    .article h3 {
        margin: 0;
    }

    .article p {
        margin: 10px 0 0;
    }

    .download-link {
color: #007BFF;
text-decoration: none;
}

.download-link:hover {
text-decoration: underline;
}
    .error {
        color: red;
    }

    .success {
        color: green;
    }
</style>

</head>
<body>

<div class="container">
    <h1>Articles collectés</h1>
    
    <?php if (count($articles) > 0): ?>
        <?php foreach ($articles as $article): ?>
            <div class="article">
                <h3><?= htmlspecialchars($article['title']) ?></h3>
                <p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
                <a href="articles.php?download_id=<?= $article['id'] ?>" class="download-link">Télécharger cet article</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        
        
        <p>Aucun article disponible.</p>
    <?php endif; ?>

    <p><a href="formulaire.php">Retour au formulaire</a></p>
</div>

</body>
</html>