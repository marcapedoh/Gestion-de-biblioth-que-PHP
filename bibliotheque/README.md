# TP - Application de gestion d'une bibliothèque (PHP + PDO)

Application développée **en PHP procédural (sans POO)**, conforme au cahier
des charges du TP « Développement Web en PHP avec PDO », avec l'ensemble des
fonctionnalités bonus (catégories, upload de couverture, tri, export,
interface Bootstrap et journalisation).

## Installation

1. Copier le dossier `bibliotheque/` dans le répertoire racine de votre
   serveur web (ex. `htdocs/` pour XAMPP/WAMP, ou `/var/www/html/`).

2. Créer la base de données en important le script SQL fourni :

   ```
   mysql -u root -p < schema.sql
   ```

   Ce script crée la base `bibliotheque`, les 6 tables (utilisateurs, livres,
   catégories, étudiants, emprunts, journal), 2 comptes administrateur, et un
   **jeu de données complet prêt à tester** : 8 catégories, 18 livres,
   10 étudiants et 12 emprunts (dont certains en cours, en retard ou
   retournés).

3. Vérifier les identifiants de connexion à la base dans
   `config/config.php` (hôte, utilisateur, mot de passe) si besoin.

4. Vérifier la constante `BASE_URL` dans `config/config.php`. Si le projet
   est accessible via `http://localhost/bibliotheque/`, la valeur par
   défaut `/bibliotheque/` convient. Si l'application est à la racine du
   site, mettez `/`.

5. **Donner les droits d'écriture** au dossier d'upload des couvertures :

   ```
   chmod -R 775 assets/uploads/couvertures
   ```

6. Ouvrir `http://localhost/bibliotheque/login.php`.

## Comptes de démonstration

- **Email :** admin@bibliotheque.com — **Mot de passe :** admin123
- **Email :** lebonheur@bibliotheque.com — **Mot de passe :** admin123

## Structure du projet

```
bibliotheque/
├── config/
│   ├── config.php          → constantes, session, BASE_URL, upload couvertures
│   └── database.php        → connexion PDO unique (getPDO())
├── controllers/
│   ├── AuthController.php      → connexion, déconnexion, protection des pages
│   ├── LivreController.php     → validation formulaires livres + upload couverture
│   ├── CategorieController.php → validation formulaires catégories
│   ├── EtudiantController.php  → validation formulaires étudiants
│   └── EmpruntController.php   → validation formulaires emprunts
├── models/
│   ├── Utilisateur.php  → accès à la table utilisateurs
│   ├── Livre.php        → CRUD, recherche, tri, pagination, statistiques
│   ├── Categorie.php    → CRUD catégories
│   ├── Etudiant.php     → CRUD, recherche multicritère, tri, pagination
│   ├── Emprunt.php      → emprunt, retour, recherche, retards, statistiques
│   └── Journal.php      → journalisation des actions
├── includes/
│   └── ExportHelper.php → export Excel (.xls) et PDF (page imprimable)
├── views/
│   ├── partials/        → head_libs.php (Bootstrap/FontAwesome), scripts_communs.php
│   ├── menu.php          → navbar Bootstrap responsive
│   ├── auth/connexion.php
│   ├── livres/           → liste, ajouter, modifier, supprimer, disponibles, export_excel, export_pdf
│   ├── categories/       → liste, ajouter, modifier, supprimer
│   ├── etudiants/        → liste, ajouter, modifier, supprimer, export_excel, export_pdf
│   ├── emprunts/         → liste, ajouter, modifier, supprimer, retourner, retards, export_excel, export_pdf
│   └── journal/          → liste
├── assets/
│   ├── css/style.css     → styles complétant Bootstrap
│   ├── img/couverture-defaut.svg
│   └── uploads/couvertures/  → couvertures de livres uploadées
├── index.php              → tableau de bord (protégé) avec graphiques Chart.js
├── login.php
└── logout.php
```

**Important : pas de programmation orientée objet.** Chaque « modèle » et
chaque « contrôleur » est un simple fichier PHP contenant des fonctions
(pas de classes).

## Fonctionnalités de base

- ✅ Authentification (`password_hash` / `password_verify`, sessions,
  protection des pages via `protegerPage()`)
- ✅ CRUD complet Livres / Étudiants / Emprunts
- ✅ Recherche multicritère (titre/auteur/ISBN pour les livres,
  nom/prénom/email/filière pour les étudiants)
- ✅ Pagination (10 résultats par page)
- ✅ Emprunt : décrémente automatiquement le stock, refuse l'emprunt si
  quantité disponible = 0
- ✅ Retour : incrémente le stock, change le statut, enregistre la date
- ✅ Sécurité : requêtes préparées PDO partout, `htmlspecialchars()` à
  l'affichage, validation/filtrage des entrées

## Fonctionnalités bonus (nouvelles)

| Fonctionnalité | Où la trouver |
|---|---|
| Tableau de bord enrichi (statistiques + **graphiques Chart.js**) | `index.php` |
| **Gestion des catégories de livres** | `views/categories/` |
| **Upload de la couverture des livres** (JPG/PNG/WEBP, 2 Mo max) | `views/livres/ajouter.php`, `modifier.php` |
| **Tri des listes** (titre, auteur, date/année, quantité...) via les en-têtes de colonnes cliquables | `views/livres/liste.php`, `views/etudiants/liste.php`, `views/emprunts/liste.php` |
| **Export Excel (.xls)** et **Export PDF** (page imprimable) | boutons « Exporter » sur chaque liste |
| **Interface responsive Bootstrap 5** + icônes **Font Awesome** sur tous les boutons d'action | toute l'application |
| **Formulaires centrés** | tous les formulaires d'ajout/modification |
| **Journalisation des actions** (ajout, modification, suppression, connexion, emprunt, retour) | `views/journal/liste.php` |

### Détails techniques des exports

- **Excel** : génère un vrai fichier `.xls` téléchargeable directement
  (table HTML avec en-têtes MIME Excel), sans dépendance externe.
- **PDF** : ouvre une page HTML imprimable, mise en page A4, qui déclenche
  automatiquement la boîte de dialogue d'impression du navigateur.
  Il suffit de choisir **« Enregistrer au format PDF »** comme imprimante.
  C'est l'approche la plus fiable en PHP pur, sans librairie tierce à
  installer (TCPDF/FPDF ne sont pas nécessaires).

### Détails techniques de l'upload de couverture

- Validation du type MIME réel du fichier (`mime_content_type`), pas
  seulement de l'extension.
- Taille maximale : 2 Mo (configurable dans `config/config.php`).
- Nom de fichier unique généré côté serveur (`uniqid()`), aucun risque
  d'écrasement ou d'injection de chemin.
- À la modification, l'ancienne couverture est automatiquement supprimée
  du disque si elle est remplacée ou retirée.
- Si un livre n'a pas de couverture, une image par défaut
  (`assets/img/couverture-defaut.svg`) est affichée.

## Exercices complémentaires (rappel)

| Exercice | Fichier |
|---|---|
| 1. Recherche multicritère étudiants | `views/etudiants/liste.php` |
| 2. Livres disponibles uniquement | `views/livres/disponibles.php` |
| 3. Emprunts en retard | `views/emprunts/retards.php` |
| 4. Nombre de livres empruntés | Affiché sur le tableau de bord |
| 5. Blocage emprunt si quantité = 0 | `models/Emprunt.php` |

## Tests effectués

L'application a été testée de bout en bout avec un serveur PHP intégré et
une base MariaDB réelle : import du schéma, connexion/déconnexion (avec
journalisation), CRUD complet sur les 4 modules, upload effectif d'une
couverture avec vérification du fichier sur disque et de la base, tri
des listes, ajout de catégorie, retour d'emprunt avec incrémentation du
stock, export Excel et export PDF (contenu vérifié). Tout fonctionne comme
attendu.
