<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Galerie Médias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .buttons {
            margin-bottom: 20px;
        }
        button {
            padding: 10px;
            margin: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        #gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            justify-content: center;
        }
        img, video {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

    <div class="buttons">
        <button onclick="filterMedia('image')">Photos</button>
        <button onclick="filterMedia('video')">Vidéos</button>
        <button onclick="filterMedia('all')">Tout</button>
    </div>

    <div id="gallery"></div>

    <?php
    header('Content-Type: application/json');

    try {
        $conn = new PDO("pgsql:host=localhost;dbname=media_db", "user", "password");
        $query = "SELECT url, type FROM media_table";
        $stmt = $conn->query($query);
        $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<script>window.mediaData = " . json_encode($media) . ";</script>";
    } catch (Exception $e) {
        echo "<script>console.error('Erreur de connexion à la base de données');</script>";
    }
    ?>

    <script>
        function displayMedia(type) {
            const gallery = document.getElementById('gallery');
            gallery.innerHTML = '';

            window.mediaData.forEach(media => {
                if (type === 'all' || media.type === type) {
                    let element;
                    if (media.type === 'image') {
                        element = document.createElement('img');
                        element.src = media.url;
                    } else {
                        element = document.createElement('video');
                        element.src = media.url;
                        element.controls = true;
                    }
                    gallery.appendChild(element);
                }
            });
        }

        function filterMedia(type) {
            displayMedia(type);
        }

        window.onload = () => displayMedia('all');
    </script>

</body>
</html>
