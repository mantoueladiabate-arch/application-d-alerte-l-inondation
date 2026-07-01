<?php
// Connexion à la base de données PostgreSQL
$host = 'localhost';
$db = 'base_inondation';  // Remplacez par votre nom de base de données
$user = 'postgres';   // Remplacez par votre nom d'utilisateur
$pass = '0151516084';  // Remplacez par votre mot de passe



try {
 // Connexion à PostgreSQL avec PDO
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement du formulaire si soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Validation simple
    if (!empty($title) && !empty($content)) {
        // Insertion dans la base de données
        try {
            $stmt = $pdo->prepare("INSERT INTO articles (title, content) VALUES (:title, :content)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->execute();
            echo "<p class='success'>Article ajouté avec succès !</p>";
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de l'ajout de l'article : " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>Veuillez remplir tous les champs.</p>";
    }
}

// Récupération des articles existants
$query = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC");
$articles = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collecte d'articles</title>
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
    <h1>Collecte d'articles</h1>

    <!-- Affichage des messages -->
    <?php if (isset($successMessage)) : ?>
        <p class="success"><?= $successMessage ?></p>
    <?php elseif (isset($errorMessage)) : ?>
        <p class="error"><?= $errorMessage ?></p>
    <?php endif; ?>

    <!-- Formulaire de collecte d'article -->
    <form action="formulaire.php" method="POST">
        <label for="title">Titre de l'article :</label>
        <input type="text" id="title" name="title" required>

        <label for="content">Contenu de l'article :</label>
        <textarea id="content" name="content" required></textarea>

        <button type="submit">Soumettre</button>
    </form>
    
 
</div>

</body>
</html>