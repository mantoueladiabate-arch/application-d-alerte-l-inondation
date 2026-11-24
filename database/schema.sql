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

-- Bilans post-crise (module fiche_bilan/)
CREATE TABLE IF NOT EXISTS bilans(
    id_bilan SERIAL PRIMARY KEY,
    id_alerte INTEGER NULL, -- Lien éventuel vers une alerte initiale

    -- Métadonnées et statut du bilan
    redacteur_nom VARCHAR(150) NOT NULL,
    redacteur_fonction VARCHAR(150),
    organisme VARCHAR(150),
    statut_bilan VARCHAR(30) DEFAULT 'Brouillon' CHECK (statut_bilan IN ('Brouillon', 'Partiel', 'Consolidé')),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date de redaction du bilan
    date_maj TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date de la mise à jour

    -- Étape 1 : Position géographique et déroulement de l'épisode
    date_debut TIMESTAMP NOT NULL,--Date de debut de l'inondation
    date_fin TIMESTAMP NULL,--Date de fin de l'inondation
    commune VARCHAR(100) NOT NULL,
    quartier VARCHAR(150) NOT NULL,
    repere_precis TEXT,
    type_milieu VARCHAR(80),
    latitude DOUBLE PRECISION NOT NULL,
    longitude DOUBLE PRECISION NOT NULL,
    geom GEOMETRY(Point, 4326),

    -- Étape 2 : Causes identifiées
    causes_meteo TEXT[],
    causes_urbanisme_drainage TEXT[],
    details_causes TEXT,

    -- Étape 3 : Bilan des dégâts causés
    nb_deces INTEGER DEFAULT 0 CHECK (nb_deces >= 0),
    nb_blesses INTEGER DEFAULT 0 CHECK (nb_blesses >= 0),
    nb_disparus INTEGER DEFAULT 0 CHECK (nb_disparus >= 0),
    nb_menages_sinistres INTEGER DEFAULT 0 CHECK (nb_menages_sinistres >= 0),
    nb_personnes_deplacees INTEGER DEFAULT 0 CHECK (nb_personnes_deplacees >= 0),
    risques_sanitaires TEXT[],
    infrastructures_endommagees TEXT[],
    nb_habitations_touchees INTEGER DEFAULT 0 CHECK (nb_habitations_touchees >= 0),
    nb_habitations_detruites INTEGER DEFAULT 0 CHECK (nb_habitations_detruites >= 0),
    activites_touchees TEXT[],
    estimation_pertes TEXT,

    -- Étape 4 : Actions prises pendant l'inondation
    actions_urgence TEXT[],
    acteurs_mobilises TEXT[],
    moyens_materiels TEXT,
    difficultes_intervention TEXT,

    -- Étape 5 : Actions post-crise (après la manifestation de la crise)
    actions_post_crise TEXT[],
    avancement_post_crise VARCHAR(50) DEFAULT 'Non engagées',

    -- Étape 6 : Actions préventives pour éviter les risques futurs
    mesures_structurelles TEXT[],--action technique à prendre pour eviter d'autre crise
    mesures_non_structurelles TEXT[],--decision gouvernementale à prendre pour eviter d'autre crise
    action_prioritaire TEXT,
    responsable_action VARCHAR(150),
    horizon_action VARCHAR(50)
);

CREATE INDEX IF NOT EXISTS idx_bilans_geom ON bilans USING GIST (geom);

CREATE OR REPLACE FUNCTION maj_bilan_geom()
RETURNS TRIGGER AS $$
BEGIN
    NEW.geom := ST_SetSRID(ST_MakePoint(NEW.longitude, NEW.latitude), 4326);
    NEW.date_maj := CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_bilans_geom ON bilans;
CREATE TRIGGER trg_bilans_geom
BEFORE INSERT OR UPDATE ON bilans
FOR EACH ROW EXECUTE FUNCTION maj_bilan_geom();
