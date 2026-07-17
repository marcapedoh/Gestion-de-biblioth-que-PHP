-- =====================================================================
-- Migration 002 : jeu de données de test (PostgreSQL)
-- À exécuter après 001_create_tables.sql. Utile pour les démos et les
-- tests unitaires de Brandon.
-- =====================================================================

-- ---------------------------------------------------------------------
-- Utilisateurs (admin de démo — mot de passe : "admin123")
-- Hash généré avec password_hash("admin123", PASSWORD_DEFAULT)
-- ---------------------------------------------------------------------
INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES
('Admin Bibliothèque', 'admin@bibliotheque.ga', '$2y$10$92IXUNpkjO0rOQ5byMi.YeIVW6HqgjcXFy2KkV5RxLzQOA3zP.qC6');

-- ---------------------------------------------------------------------
-- Livres
-- ---------------------------------------------------------------------
INSERT INTO livres (titre, auteur, isbn, annee, quantite) VALUES
('Le Petit Prince',                 'Antoine de Saint-Exupéry',  '978-2070408504', 1943, 5),
('Une si longue lettre',            'Mariama Bâ',                '978-2708707015', 1979, 3),
('Les Bouts de bois de Dieu',       'Ousmane Sembène',           '978-2266036827', 1960, 0),
('Introduction à l''algorithmique', 'Cormen, Leiserson, Rivest', '978-2100547870', 2010, 4),
('Bases de données : concepts',     'Raghu Ramakrishnan',        '978-2100482639', 2001, 2),
('Le Roi Christophe',               'Aimé Césaire',              '978-2020053069', 1963, 1);

-- ---------------------------------------------------------------------
-- Étudiants
-- ---------------------------------------------------------------------
INSERT INTO etudiants (nom, prenom, email, telephone, filiere) VALUES
('OBAME',   'Sarah',    'sarah.obame@iai-gabon.ga',   '074123456', 'ING2 - Génie Logiciel'),
('MBOUMBA', 'Junior',   'junior.mboumba@iai-gabon.ga','066987654', 'ING2 - Réseaux'),
('NDONG',   'Chloé',    'chloe.ndong@iai-gabon.ga',   '077456123', 'ING1 - Génie Logiciel'),
('ISSEMBE', 'David',    'david.issembe@iai-gabon.ga', '062334455', 'ING2 - Data Science');

-- ---------------------------------------------------------------------
-- Emprunts (mélange : en cours, en retard, retourné)
-- ---------------------------------------------------------------------
INSERT INTO emprunts (id_livre, id_etudiant, date_emprunt, date_retour_prevue, date_retour, statut) VALUES
(1, 1, '2026-06-01', '2026-06-15', '2026-06-14', 'Retourné'),
(4, 2, '2026-07-01', '2026-07-15', NULL,         'En cours'),
(2, 3, '2026-06-20', '2026-07-04', NULL,         'En cours'), -- en retard si date du jour > 2026-07-04
(5, 4, '2026-07-10', '2026-07-24', NULL,         'En cours');
