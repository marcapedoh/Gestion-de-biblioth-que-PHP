-- =====================================================================
-- Migration 004 (BONUS) : gestion des catégories de livres (PostgreSQL)
-- =====================================================================

CREATE TABLE IF NOT EXISTS categories (
    id  SERIAL PRIMARY KEY,
    nom VARCHAR(80) NOT NULL UNIQUE
);

-- On ajoute la relation sur livres (nullable : un livre peut ne pas
-- encore être catégorisé)
ALTER TABLE livres
    ADD COLUMN IF NOT EXISTS categorie_id INT NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.table_constraints
        WHERE constraint_name = 'fk_livres_categorie'
    ) THEN
        ALTER TABLE livres
            ADD CONSTRAINT fk_livres_categorie
            FOREIGN KEY (categorie_id) REFERENCES categories(id)
            ON DELETE SET NULL ON UPDATE CASCADE;
    END IF;
END $$;

-- Jeu de données de test
INSERT INTO categories (nom) VALUES
('Littérature'), ('Informatique'), ('Sciences'), ('Histoire'), ('Économie')
ON CONFLICT (nom) DO NOTHING;

-- Exemple de rattachement (à adapter selon les ids réels après le seed 002)
-- UPDATE livres SET categorie_id = 1 WHERE titre = 'Le Petit Prince';
