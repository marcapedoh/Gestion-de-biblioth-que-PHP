-- =====================================================================
-- Migration 001 : création des tables (PostgreSQL)
-- Bibliothèque universitaire — TP PHP/PDO (IAI Gabon, ING2)
--
-- La base "bibliotheque" doit déjà exister (créée via pgAdmin ou :
--   createdb bibliotheque
-- ). Ce script s'exécute une fois connecté DESSUS (pgAdmin : sélectionner
-- la base "bibliotheque" avant d'ouvrir le Query Tool -- pas de "USE" en
-- PostgreSQL, contrairement à MySQL).
-- =====================================================================

-- ---------------------------------------------------------------------
-- Table utilisateurs (comptes administrateurs)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
    id            SERIAL PRIMARY KEY,
    nom           VARCHAR(50)  NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe  VARCHAR(255) NOT NULL, -- toujours un hash password_hash(), jamais en clair
    created_at    TIMESTAMP DEFAULT NOW()
);

-- ---------------------------------------------------------------------
-- Table livres
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS livres (
    id       SERIAL PRIMARY KEY,
    titre    VARCHAR(150) NOT NULL,
    auteur   VARCHAR(100) NOT NULL,
    isbn     VARCHAR(20)  NOT NULL,
    annee    INT          NOT NULL,
    quantite INT          NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_livres_titre  ON livres (titre);
CREATE INDEX IF NOT EXISTS idx_livres_auteur ON livres (auteur);
CREATE INDEX IF NOT EXISTS idx_livres_isbn   ON livres (isbn);

-- ---------------------------------------------------------------------
-- Table etudiants
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS etudiants (
    id        SERIAL PRIMARY KEY,
    nom       VARCHAR(50)  NOT NULL,
    prenom    VARCHAR(50)  NOT NULL,
    email     VARCHAR(100) NOT NULL,
    telephone VARCHAR(30),
    filiere   VARCHAR(100)
);

CREATE INDEX IF NOT EXISTS idx_etudiants_nom   ON etudiants (nom);
CREATE INDEX IF NOT EXISTS idx_etudiants_email ON etudiants (email);

-- ---------------------------------------------------------------------
-- Table emprunts
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS emprunts (
    id                  SERIAL PRIMARY KEY,
    id_livre            INT NOT NULL REFERENCES livres(id)
                            ON DELETE RESTRICT ON UPDATE CASCADE,
    id_etudiant         INT NOT NULL REFERENCES etudiants(id)
                            ON DELETE RESTRICT ON UPDATE CASCADE,
    date_emprunt        DATE NOT NULL,
    date_retour_prevue  DATE NOT NULL,
    date_retour         DATE NULL,
    statut              VARCHAR(20) NOT NULL DEFAULT 'En cours' -- 'En cours' | 'Retourné'
);

CREATE INDEX IF NOT EXISTS idx_emprunts_statut ON emprunts (statut);
CREATE INDEX IF NOT EXISTS idx_emprunts_date_retour_prevue ON emprunts (date_retour_prevue);

-- ---------------------------------------------------------------------
-- Jeu de données de test (optionnel, voir 002_seed_data.sql)
-- ---------------------------------------------------------------------
