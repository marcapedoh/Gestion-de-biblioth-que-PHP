-- ============================================================
-- Base de données : gestion d'une bibliothèque universitaire
-- Version 2 : catégories, couverture des livres, journalisation
-- ============================================================

CREATE DATABASE IF NOT EXISTS bibliotheque
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE bibliotheque;

-- Table des administrateurs (utilisateurs de l'application)
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL
);

-- Table des catégories de livres (Bonus : gestion des catégories)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL
);

-- Table des livres
-- categorie_id : catégorie du livre (nullable, conservée si la catégorie est supprimée)
-- couverture   : nom du fichier image stocké dans assets/uploads/couvertures/
CREATE TABLE livres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    isbn VARCHAR(20),
    annee INT,
    quantite INT NOT NULL DEFAULT 0,
    categorie_id INT NULL,
    couverture VARCHAR(255) NULL,
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Table des étudiants
CREATE TABLE etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    telephone VARCHAR(30),
    filiere VARCHAR(100)
);

-- Table des emprunts
CREATE TABLE emprunts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_livre INT NOT NULL,
    id_etudiant INT NOT NULL,
    date_emprunt DATE NOT NULL,
    date_retour_prevue DATE NOT NULL,
    date_retour DATE NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'En cours',
    FOREIGN KEY (id_livre) REFERENCES livres(id),
    FOREIGN KEY (id_etudiant) REFERENCES etudiants(id)
);

-- Table de journalisation des actions (Bonus : traçabilité)
CREATE TABLE journal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NULL,
    utilisateur_nom VARCHAR(50) NULL,
    action VARCHAR(20) NOT NULL,      -- Ajout / Modification / Suppression / Connexion / Déconnexion / Emprunt / Retour
    module VARCHAR(50) NOT NULL,      -- Livres / Étudiants / Emprunts / Catégories / Authentification
    description VARCHAR(255) NOT NULL,
    date_action DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Compte administrateur de démonstration
-- Email : admin@bibliotheque.com
-- Mot de passe en clair : admin123
-- ------------------------------------------------------------
INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES
('Administrateur', 'admin@bibliotheque.com', '$2b$10$GiPkTT/xUpo40.4pis8kT.zqnnd1UzCoUsmTmdex8H5eAdj3QDche'),
('Le Bonheur', 'lebonheur@bibliotheque.com', '$2b$10$GiPkTT/xUpo40.4pis8kT.zqnnd1UzCoUsmTmdex8H5eAdj3QDche');

-- ------------------------------------------------------------
-- Catégories de livres
-- ------------------------------------------------------------
INSERT INTO categories (nom, description) VALUES
('Roman', 'Œuvres de fiction narrative'),
('Informatique & Développement', 'Programmation, algorithmique, génie logiciel'),
('Sciences', 'Physique, mathématiques, sciences naturelles'),
('Histoire', 'Ouvrages historiques et essais'),
('Poésie', 'Recueils de poèmes'),
('Bandes dessinées', 'BD et comics'),
('Droit & Économie', 'Droit, gestion et économie'),
('Philosophie', 'Essais et traités philosophiques');

-- ------------------------------------------------------------
-- Livres (jeu de données complet pour les tests)
-- ------------------------------------------------------------
INSERT INTO livres (titre, auteur, isbn, annee, quantite, categorie_id, couverture) VALUES
('Le Petit Prince', 'Antoine de Saint-Exupéry', '9782070408504', 1943, 5, 1, NULL),
('1984', 'George Orwell', '9780451524935', 1949, 2, 1, NULL),
('Clean Code', 'Robert C. Martin', '9780132350884', 2008, 4, 2, NULL),
('Le Comte de Monte-Cristo', 'Alexandre Dumas', '9782253097337', 1844, 0, 1, NULL),
('Introduction à l''Algorithmique', 'Cormen, Leiserson, Rivest, Stein', '9780262033848', 2009, 3, 2, NULL),
('Une Breve Histoire du Temps', 'Stephen Hawking', '9782081215916', 1988, 2, 3, NULL),
('Sapiens : Une Breve Histoire de l''Humanite', 'Yuval Noah Harari', '9782226257017', 2011, 3, 4, NULL),
('Les Miserables', 'Victor Hugo', '9782253096344', 1862, 3, 1, NULL),
('Design Patterns', 'Gang of Four (Gamma, Helm, Johnson, Vlissides)', '9780201633610', 1994, 2, 2, NULL),
('Asterix chez les Gaulois', 'Goscinny & Uderzo', '9782012101331', 1961, 5, 6, NULL),
('Le Prince', 'Nicolas Machiavel', '9782080712706', 1532, 2, 8, NULL),
('Discours de la Methode', 'René Descartes', '9782081231619', 1637, 0, 8, NULL),
('Code Civil Gabonais Annote', 'Collectif', '9782940123456', 2015, 3, 7, NULL),
('L''Etranger', 'Albert Camus', '9782070360024', 1942, 2, 1, NULL),
('Les Fleurs du Mal', 'Charles Baudelaire', '9782070409724', 1857, 2, 5, NULL),
('Le Rouge et le Noir', 'Stendhal', '9782253096139', 1830, 1, 1, NULL),
('Une Breve Histoire de Presque Tout', 'Bill Bryson', '9782757813560', 2003, 2, 3, NULL),
('Structure et Interpretation des Programmes', 'Abelson & Sussman', '9780262510875', 1985, 1, 2, NULL);

-- ------------------------------------------------------------
-- Étudiants
-- ------------------------------------------------------------
INSERT INTO etudiants (nom, prenom, email, telephone, filiere) VALUES
('Ndong', 'Marie', 'marie.ndong@univ.ga', '077102030', 'Informatique'),
('Obame', 'Jean', 'jean.obame@univ.ga', '066607080', 'Gestion'),
('Ntoutoume', 'Léa Mireille', 'lea.ntoutoume@univ.ga', '074455667', 'Informatique'),
('Moussavou', 'Steve Aubin', 'steve.moussavou@univ.ga', '062334455', 'Réseaux & Télécoms'),
('Okouyi', 'Sandrine', 'sandrine.okouyi@univ.ga', '077889900', 'Droit'),
('Bongo', 'Franck Ivan', 'franck.bongo@univ.ga', '066112233', 'Informatique'),
('Meye', 'Divine', 'divine.meye@univ.ga', '074998877', 'Économie'),
('Nzue', 'Aristide', 'aristide.nzue@univ.ga', '062556677', 'Gestion'),
('Boussamba', 'Christevie', 'christevie.boussamba@univ.ga', '077223344', 'Informatique'),
('Ella', 'Patrick', 'patrick.ella@univ.ga', '066998811', 'Réseaux & Télécoms');

-- ------------------------------------------------------------
-- Emprunts (mélange : en cours, retournés, en retard)
-- Dates calculées par rapport à la date d'exécution du script
-- ------------------------------------------------------------
INSERT INTO emprunts (id_livre, id_etudiant, date_emprunt, date_retour_prevue, date_retour, statut) VALUES
(2, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), NULL, 'En cours'),
(4, 1, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 6 DAY), NULL, 'En cours'),
(12, 5, DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_SUB(CURDATE(), INTERVAL 11 DAY), NULL, 'En cours'),
(16, 6, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), NULL, 'En cours'),
(9, 3, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 16 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY), 'Retourné'),
(1, 7, DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 26 DAY), DATE_SUB(CURDATE(), INTERVAL 27 DAY), 'Retourné'),
(3, 8, DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 1 DAY), NULL, 'En cours'),
(7, 9, DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(CURDATE(), INTERVAL 46 DAY), DATE_SUB(CURDATE(), INTERVAL 44 DAY), 'Retourné'),
(10, 4, DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 6 DAY), NULL, 'En cours'),
(18, 10, DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_SUB(CURDATE(), INTERVAL 31 DAY), DATE_SUB(CURDATE(), INTERVAL 29 DAY), 'Retourné'),
(11, 2, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 4 DAY), NULL, 'En cours'),
(13, 6, DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), NULL, 'En cours');

-- ------------------------------------------------------------
-- Journal des actions (exemples pour tester la page Journal)
-- ------------------------------------------------------------
INSERT INTO journal (utilisateur_id, utilisateur_nom, action, module, description, date_action) VALUES
(1, 'Administrateur', 'Connexion', 'Authentification', 'Connexion réussie à l''application', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 'Administrateur', 'Ajout', 'Livres', 'Ajout du livre "Clean Code"', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 'Administrateur', 'Ajout', 'Étudiants', 'Ajout de l''étudiant Marie Ndong', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 'Administrateur', 'Modification', 'Livres', 'Modification du livre "1984" (quantité)', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 'Administrateur', 'Ajout', 'Catégories', 'Ajout de la catégorie "Informatique & Développement"', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 'Administrateur', 'Emprunt', 'Emprunts', 'Emprunt du livre "1984" par Jean Obame', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'Administrateur', 'Retour', 'Emprunts', 'Retour du livre "Design Patterns" par Léa Mireille Ntoutoume', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'Administrateur', 'Suppression', 'Étudiants', 'Suppression d''un étudiant test', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'Le Bonheur', 'Connexion', 'Authentification', 'Connexion réussie à l''application', NOW());
