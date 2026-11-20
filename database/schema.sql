-- Schéma reconstitué par analyse du code PHP (aucun schéma officiel n'existait dans le dépôt).
-- Appliqué une seule fois lors de l'installation locale (MAMP + Postgres via Homebrew).

CREATE EXTENSION IF NOT EXISTS postgis;

CREATE TABLE IF NOT EXISTS communes_abidjan (
  id SERIAL PRIMARY KEY,
  commune TEXT NOT NULL,
  geom GEOMETRY(MultiPolygon, 3857)
);

CREATE TABLE IF NOT EXISTS quartier (
  id SERIAL PRIMARY KEY,
  nom_quartier TEXT NOT NULL,
  id_commune INTEGER REFERENCES communes_abidjan(id),
  geom GEOMETRY(Geometry, 4326)
);

-- Table à plat utilisée par le module statistique/ (distincte de `quartier`, pas de FK en base,
-- jointe côté PHP sur le nom de commune).
CREATE TABLE IF NOT EXISTS quartiers_abidjan (
  commune TEXT,
  nom_quart TEXT
);

CREATE TABLE IF NOT EXISTS informations (
  id SERIAL PRIMARY KEY,
  commune INTEGER REFERENCES communes_abidjan(id),
  quartier TEXT,
  risques TEXT CHECK (risques IN ('Inondation','Erosion','Eboulement','Effondrement')),
  description TEXT,
  fichier TEXT,
  longitude DOUBLE PRECISION,
  latitude DOUBLE PRECISION,
  date TIMESTAMP DEFAULT NOW(),
  new_statut TEXT NOT NULL DEFAULT 'non_traite' CHECK (new_statut IN ('non_traite','traite')),
  recommandation TEXT,
  last_updated TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contacts (
  id SERIAL PRIMARY KEY,
  nom TEXT NOT NULL,
  prenom TEXT NOT NULL,
  contact1 TEXT NOT NULL,
  whatsapp TEXT,
  mail TEXT
);

CREATE TABLE IF NOT EXISTS utilisateurs (
  id SERIAL PRIMARY KEY,
  nom TEXT NOT NULL,
  prenom TEXT NOT NULL,
  user_name TEXT NOT NULL UNIQUE,
  contact1 TEXT NOT NULL,
  mail TEXT NOT NULL UNIQUE,
  mot_de_passe TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'analyste' CHECK (role IN ('admin','administrateur','analyste')),
  date_creation TIMESTAMP DEFAULT NOW()
);
