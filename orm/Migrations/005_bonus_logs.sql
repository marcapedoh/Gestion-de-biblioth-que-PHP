-- =====================================================================
-- Migration 005 (BONUS) : journalisation des actions (PostgreSQL)
-- Table utilisée par core/Log/Logger.php (proposition de Marc, cf.
-- README §5). Chaque Service backend appelle Logger::log() après une
-- action (ajout/modification/suppression), qui fait un INSERT ici via
-- AbstractRepository (aucun SQL brut en dehors de orm/, même pour les
-- logs).
-- =====================================================================

CREATE TABLE IF NOT EXISTS logs (
    id             SERIAL PRIMARY KEY,
    utilisateur_id INT NULL REFERENCES utilisateurs(id)
                        ON DELETE SET NULL ON UPDATE CASCADE,
    action         VARCHAR(50)  NOT NULL,   -- ex: 'creation', 'modification', 'suppression'
    entite         VARCHAR(50)  NOT NULL,   -- ex: 'livre', 'etudiant', 'emprunt'
    entite_id      INT NULL,
    details        VARCHAR(255) NULL,
    created_at     TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_logs_entite      ON logs (entite, entite_id);
CREATE INDEX IF NOT EXISTS idx_logs_created_at  ON logs (created_at);
