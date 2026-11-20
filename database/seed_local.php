<?php
// Seed unique pour l'installation locale : charge les communes d'Abidjan et les quartiers
// de Cocody (seules données géographiques présentes dans /data) et crée le compte admin.
// A exécuter une fois en CLI : php database/seed_local.php

require_once __DIR__ . '/../config.php';

$pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "== Communes d'Abidjan ==\n";
$communes = json_decode(file_get_contents(__DIR__ . '/../data/communes_abidjan.geojson'), true);

$insertCommune = $pdo->prepare(
    "INSERT INTO communes_abidjan (id, commune, geom)
     VALUES (:id, :commune, ST_Transform(ST_SetSRID(ST_GeomFromGeoJSON(:geom), 4326), 3857))
     ON CONFLICT (id) DO NOTHING"
);

foreach ($communes['features'] as $f) {
    $insertCommune->execute([
        ':id'      => $f['properties']['OBJECTID'],
        ':commune' => $f['properties']['COMMUNE'],
        ':geom'    => json_encode($f['geometry']),
    ]);
    echo "  - {$f['properties']['COMMUNE']} (id {$f['properties']['OBJECTID']})\n";
}
$pdo->exec("SELECT setval('communes_abidjan_id_seq', (SELECT MAX(id) FROM communes_abidjan))");

echo "\n== Quartiers de Cocody ==\n";
$quartiers = json_decode(file_get_contents(__DIR__ . '/../data/quartiers_cocody.geojson'), true);

$insertQuartier = $pdo->prepare(
    "INSERT INTO quartier (nom_quartier, id_commune, geom)
     VALUES (:nom, :commune_id, ST_SetSRID(ST_GeomFromGeoJSON(:geom), 4326))"
);
$insertQuartierFlat = $pdo->prepare(
    "INSERT INTO quartiers_abidjan (commune, nom_quart) VALUES ('COCODY', :nom)"
);

$count = 0;
foreach ($quartiers['features'] as $f) {
    $nom = $f['properties']['nom_quar'] ?? null;
    if (!$nom) continue;
    $insertQuartier->execute([
        ':nom'        => $nom,
        ':commune_id' => (int)($f['properties']['commune_id'] ?? 4),
        ':geom'       => json_encode($f['geometry']),
    ]);
    $insertQuartierFlat->execute([':nom' => $nom]);
    $count++;
}
echo "  - $count quartiers insérés (Cocody uniquement — c'est la seule donnée de quartier disponible dans le dépôt)\n";

echo "\n== Compte admin ==\n";
$check = $pdo->prepare("SELECT id FROM utilisateurs WHERE user_name = 'admin'");
$check->execute();
if ($check->fetch()) {
    echo "  - déjà présent, rien à faire\n";
} else {
    $stmt = $pdo->prepare(
        "INSERT INTO utilisateurs (nom, prenom, user_name, contact1, mail, mot_de_passe, role)
         VALUES ('Admin', 'Local', 'admin', '0700000000', 'admin@local.test', :mdp, 'admin')"
    );
    $stmt->execute([':mdp' => password_hash('admin1234', PASSWORD_DEFAULT)]);
    echo "  - créé : user_name=admin / mot de passe=admin1234\n";
}

echo "\nTerminé.\n";
