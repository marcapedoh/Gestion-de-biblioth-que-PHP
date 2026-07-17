-- =====================================================================
-- Migration 003 : fonctions, triggers (PostgreSQL / PL/pgSQL)
-- Couvre les exercices complémentaires 3, 4, 5 directement en base,
-- en complément du code PHP (orm/QueryBuilder + Services).
-- =====================================================================

-- ---------------------------------------------------------------------
-- FONCTION : nombre total de livres actuellement empruntés (Exercice 4)
-- Utilisation : SELECT fn_total_livres_empruntes();
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION fn_total_livres_empruntes()
RETURNS INT AS $$
DECLARE
    total INT;
BEGIN
    SELECT COUNT(*) INTO total
    FROM emprunts
    WHERE statut = 'En cours';

    RETURN total;
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------
-- FONCTION (retourne un jeu de résultats) : emprunts en retard (Exercice 3)
-- Utilisation : SELECT * FROM fn_emprunts_en_retard();
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION fn_emprunts_en_retard()
RETURNS TABLE (
    emprunt_id          INT,
    titre               VARCHAR,
    nom_etudiant        VARCHAR,
    prenom_etudiant      VARCHAR,
    date_emprunt        DATE,
    date_retour_prevue  DATE,
    jours_de_retard     INT
) AS $$
BEGIN
    RETURN QUERY
    SELECT e.id, l.titre, et.nom, et.prenom, e.date_emprunt, e.date_retour_prevue,
           (CURRENT_DATE - e.date_retour_prevue)::INT AS jours_de_retard
    FROM emprunts e
    JOIN livres l     ON l.id = e.id_livre
    JOIN etudiants et ON et.id = e.id_etudiant
    WHERE e.statut = 'En cours'
      AND e.date_retour_prevue < CURRENT_DATE
    ORDER BY jours_de_retard DESC;
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------
-- FONCTION : enregistrer le retour d'un livre
-- Met à jour l'emprunt ET remet le livre en stock (le trigger
-- trg_emprunts_after_update ci-dessous s'occupe du stock automatiquement)
-- Utilisation : SELECT sp_retourner_livre(2); -- id de l'emprunt
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION sp_retourner_livre(p_emprunt_id INT)
RETURNS VOID AS $$
BEGIN
    UPDATE emprunts
    SET statut = 'Retourné', date_retour = CURRENT_DATE
    WHERE id = p_emprunt_id AND statut = 'En cours';
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------
-- TRIGGER : bloque l'emprunt si le stock est à 0 et décrémente le stock
-- sinon, à la création d'un emprunt (Exercice 5, filet de sécurité côté
-- base — la vérification principale reste dans EmpruntService.php)
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION trg_fn_emprunts_before_insert()
RETURNS TRIGGER AS $$
DECLARE
    stock_disponible INT;
BEGIN
    SELECT quantite INTO stock_disponible
    FROM livres
    WHERE id = NEW.id_livre
    FOR UPDATE;

    IF stock_disponible IS NULL OR stock_disponible <= 0 THEN
        RAISE EXCEPTION 'Emprunt refusé : quantité disponible = 0.';
    END IF;

    UPDATE livres SET quantite = quantite - 1 WHERE id = NEW.id_livre;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_emprunts_before_insert ON emprunts;
CREATE TRIGGER trg_emprunts_before_insert
    BEFORE INSERT ON emprunts
    FOR EACH ROW
    EXECUTE FUNCTION trg_fn_emprunts_before_insert();

-- ---------------------------------------------------------------------
-- TRIGGER : réincrémente le stock quand un livre est retourné
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION trg_fn_emprunts_after_update()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.statut = 'En cours' AND NEW.statut = 'Retourné' THEN
        UPDATE livres SET quantite = quantite + 1 WHERE id = NEW.id_livre;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_emprunts_after_update ON emprunts;
CREATE TRIGGER trg_emprunts_after_update
    AFTER UPDATE ON emprunts
    FOR EACH ROW
    EXECUTE FUNCTION trg_fn_emprunts_after_update();

-- ---------------------------------------------------------------------
-- Exemples d'appel (à titre indicatif)
-- ---------------------------------------------------------------------
-- SELECT fn_total_livres_empruntes();
-- SELECT * FROM fn_emprunts_en_retard();
-- SELECT sp_retourner_livre(2);
