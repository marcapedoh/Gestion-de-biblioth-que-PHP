# backend/ — Dev Backend (LeBonheur)

Réécriture **procédurale** (aucune classe créée ici), à la demande de
l'équipe : uniquement des fonctions, des tableaux associatifs et des
`array` de résultat (`['success' => bool, 'errors' => [...], ...]`) au lieu
d'exceptions personnalisées ou d'entités objet.

## Organisation

| Fichier | Contenu |
|---|---|
| `support/validator.php` | Fonctions de validation (`valider_requis`, `valider_email`, ...) et `nettoyer_chaine()` |
| `support/vue.php` | `vue(string $composant, array $data): Response` — inclut un composant `frontend/`, capture le HTML, le renvoie en `Response` |
| `livre.php` | `livre_lister/trouver/disponibles/creer/modifier/supprimer/valider` |
| `etudiant.php` | Idem pour les étudiants (recherche multicritère intégrée à `etudiant_lister`) |
| `emprunt.php` | `emprunt_enregistrer/retourner/lister_avec_details/en_retard/total_livres_empruntes/supprimer` |
| `auth.php` | `auth_connecter/deconnecter/est_connecte/utilisateur_connecte` |
| `controllers/*.php` | Une fonction par route : `function (Request $request): Response` |
| `routes.php` | Enregistre toutes les routes ci-dessus sur un `Core\Http\Router` |

## Pourquoi pas de classes, même pour l'accès aux données ?

`Orm\Repository\AbstractRepository` (Adamou) s'utilise normalement par
héritage (`class LivreRepository extends AbstractRepository`), ce qui est
de la POO. Pour rester 100 % procédural, ce backend n'hérite d'aucune
classe orm/ : il utilise directement `Orm\Connection\Database::getInstance()`
(PDO) pour le CRUD simple, et `Orm\QueryBuilder\QueryBuilder` (utilisé
comme simple outil, sans le sous-classer) pour les recherches/filtres.
**Conséquence à connaître :** `orm/Entity/` et `orm/Repository/AbstractRepository`
ne sont donc pas utilisés par ce backend — à en discuter avec Adamou si ça
pose un souci pour la cohérence globale du projet.

## ⚠️ Deux points à régler avec le reste de l'équipe

1. **Autoload des fonctions (Marc / composer.json).** Composer n'autoload
   automatiquement que des classes (PSR-4). Des fichiers de fonctions comme
   ceux-ci doivent être chargés explicitement. Deux options :
   - ajouter dans `composer.json` :
     ```json
     "autoload": {
         "files": [
             "backend/support/validator.php",
             "backend/support/vue.php"
         ]
     }
     ```
     (les autres fichiers `backend/*.php` et `backend/controllers/*.php`
     s'incluent déjà eux-mêmes via `require_once`, donc pas obligatoire de
     les lister, mais ça évite les surprises) ;
   - ou simplement `require_once __DIR__ . '/../backend/routes.php'` depuis
     `public/index.php`, qui entraîne déjà tous les autres `require_once`
     en cascade.

2. **`Core\Kernel` ne charge aucune route pour l'instant.** `Kernel::run()`
   crée son propre `Router` vide en interne — il n'y a aujourd'hui aucun
   moyen d'y injecter les routes de `backend/routes.php`. Il faut que Marc
   fasse évoluer `Kernel` pour accepter un `Router` externe (ou exposer le
   sien avant `run()`). En attendant, `routes.php` est prêt à être branché
   dès que ce sera possible.

## Contrat avec le frontend (BARESSEY)

| Composant (`frontend/components/...`) | Variables reçues |
|---|---|
| `livre-liste` | `$livres`, `$pagination` (`data,total,page,per_page,total_pages`), `$recherche` |
| `livre-form` | `$livre` (array\|null), `$errors`, `$old` |
| `etudiant-liste` | `$etudiants`, `$pagination`, `$recherche` |
| `etudiant-form` | `$etudiant` (array\|null), `$errors`, `$old` |
| `emprunt-liste` | `$emprunts` (lignes jointes livre/étudiant), `$pagination`, `$recherche`, `$modeRetard` (bool) |
| `emprunt-form` | `$livresDisponibles`, `$etudiants`, `$errors` |
| `connexion` *(à créer, absent de la liste initiale de composants)* | `$errors` |

`$errors['general']` = erreur non liée à un champ précis (identifiants
invalides, emprunt refusé). Rappel sécurité pour les vues : toujours
`htmlspecialchars()` à l'affichage.

## Confirmation : zéro classe

Aucun `class`, `interface` ou `trait` n'est déclaré dans `backend/` (vérifiable
avec `grep -rn "^\s*class\|^\s*interface\|^\s*trait" backend/` → aucun résultat).
Seules des fonctions, des tableaux associatifs, des constantes et des
fichiers `require_once`. Les seules classes utilisées sont celles fournies
par `orm/` et `core/` (Database, QueryBuilder, Request, Response, Router) —
utilisées telles quelles, jamais étendues ni instanciées par héritage.

## Couverture du cahier des charges (fichier PDF du sujet)

| Exigence du sujet | Où c'est fait |
|---|---|
| Connexion / déconnexion / pages protégées | `auth.php` + garde `auth_est_connecte()` en tête de chaque fonction de route protégée |
| Mots de passe hashés (`password_hash`/`password_verify`) | `auth_connecter()` |
| CRUD Livres (ajouter/modifier/supprimer/afficher) | `livre.php` |
| CRUD Étudiants | `etudiant.php` |
| CRUD Emprunts (enregistrer/retourner/consulter) | `emprunt.php` |
| **Rechercher un emprunt** *(exigence de base, section "Gestion des emprunts" — pas un exercice complémentaire)* | `emprunt_lister()` — recherche sur nom/prénom étudiant, titre du livre, ou statut |
| Recherche livres (titre/auteur/ISBN) | `livre_lister()` |
| Pagination (10/page) | `livre_lister()`, `etudiant_lister()`, `emprunt_lister()` |
| Sécurité : PDO, requêtes préparées, sessions, validation | Partout — voir section Sécurité ci-dessous |

## Exercices complémentaires — où ils sont couverts

| Exercice | Où |
|---|---|
| 1. Recherche multicritère étudiants | `etudiant_lister()` |
| 2. Livres disponibles | `livre_disponibles()` |
| 3. Emprunts en retard | `emprunt_en_retard()` (fonction PL/pgSQL `fn_emprunts_en_retard()`) |
| 4. Total livres empruntés | `emprunt_total_livres_empruntes()` (fonction `fn_total_livres_empruntes()`) |
| 5. Refus d'emprunt si quantité = 0 | `emprunt_enregistrer()` (contrôle PHP) + trigger `trg_emprunts_before_insert` (filet de sécurité DB) |

## Sécurité (rappel du sujet, appliqué ici)

- Toutes les requêtes passent par des requêtes préparées PDO (`Database::getInstance()->prepare()`) ou par `QueryBuilder` — jamais de valeur concaténée dans le SQL.
- Mots de passe : `password_hash()` / `password_verify()` (`auth.php`), jamais stockés en clair.
- Sessions : `session_regenerate_id(true)` à la connexion, destruction complète au logout.
- Validation systématique côté serveur avant tout `INSERT`/`UPDATE` (`support/validator.php`).
- `nettoyer_chaine()` (trim + strip_tags) avant stockage ; `htmlspecialchars()` reste à la charge des vues à l'affichage.
