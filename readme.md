# 📚 Bibliothèque Universitaire — Framework PHP Maison

Mini-projet intégrateur PHP natif, conçu comme un **mini-framework MVC** avec ORM maison,
annotations, middleware d'auth et composants front à la Angular. Base de données hébergée
sur **Render** (accessible à toute l'équipe).

**Durée : 3 semaines max** — voir planning en bas de document.

---

## 1. Équipe & rôles

| Membre | Rôle | Dossier(s) sous sa responsabilité |
|---|---|---|
| **Marc (toi)** | Architecte logiciel, gestion Git, QA code | `core/`, revue de tout le dépôt |
| **ADAMOU Youssouf** | BD & ORM | `orm/` |
| **BARESSEY** | Dev Frontend | `frontend/` |
| **LeBonheur** | Dev Backend | `backend/` |
| **Brandon** | Testeur | `tests/`, `.github/workflows/` |

Règle d'or : **chaque équipe ne touche qu'à son dossier**, mais peut *importer* ce qui vient de
`core/` et `orm/`. Personne d'autre que toi ne merge dans `main`.

---

## 2. Arborescence du dépôt

```
bibliotheque-app/
├── core/                          # Marc (Architecte)
│   ├── Http/
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── Router.php
│   ├── Auth/
│   │   └── AuthMiddleware.php
│   ├── Annotations/
│   │   ├── Controller.php         # #[Controller]
│   │   ├── Service.php            # #[Service]
│   │   └── Route.php              # #[Route("/livres", method: "GET")]
│   ├── Console/
│   │   ├── make-component.php     # commande CLI: php console make:component NomComposant
│   │   └── ComponentGenerator.php
│   └── Kernel.php
│
├── orm/                            # ADAMOU Youssouf (BD & ORM)
│   ├── Connection/
│   │   └── Database.php            # PDO singleton, lit config/database.php
│   ├── Entity/
│   │   └── AbstractEntity.php
│   ├── Repository/
│   │   └── AbstractRepository.php  # save(), find($id), findBy(), findAll(), update(), delete()
│   ├── QueryBuilder/
│   │   └── QueryBuilder.php
│   └── Migrations/
│       └── 001_create_tables.sql
│
├── backend/                        # LeBonheur (Dev Backend)
│   ├── Model/
│   │   ├── Livre.php
│   │   ├── Etudiant.php
│   │   ├── Emprunt.php
│   │   └── Utilisateur.php
│   ├── Repository/
│   │   ├── LivreRepository.php     # extends AbstractRepository
│   │   ├── EtudiantRepository.php
│   │   ├── EmpruntRepository.php
│   │   └── UtilisateurRepository.php
│   ├── Service/
│   │   ├── LivreService.php
│   │   ├── EtudiantService.php
│   │   ├── EmpruntService.php      # logique métier (ex: refuser emprunt si quantité = 0)
│   │   └── AuthService.php
│   └── Controller/
│       ├── LivreController.php
│       ├── EtudiantController.php
│       ├── EmpruntController.php
│       └── AuthController.php
│
├── frontend/                       # BARESSEY (Dev Frontend)
│   └── components/
│       ├── layout/
│       │   ├── layout.component.php
│       │   ├── layout.component.html   # menu: Accueil / Livres / Étudiants / Emprunts / Déconnexion
│       │   └── layout.component.css
│       ├── livre-liste/
│       │   ├── livre-liste.component.php
│       │   ├── livre-liste.component.html
│       │   └── livre-liste.component.css
│       ├── livre-form/
│       ├── etudiant-liste/
│       ├── etudiant-form/
│       ├── emprunt-liste/
│       ├── emprunt-form/
│       ├── search-bar/
│       └── pagination/
│
├── tests/                          # Brandon (Testeur)
│   ├── Unit/
│   │   ├── Orm/
│   │   ├── Service/
│   │   └── Repository/
│   └── phpunit.xml
│
├── public/
│   ├── index.php                   # point d'entrée unique, dispatch vers Router
│   └── assets/
│       ├── css/
│       └── js/
│
├── config/
│   ├── config.php
│   └── database.php                # lit les variables d'env (credentials Render)
│
├── .github/
│   └── workflows/
│       └── ci.yml                  # PHPUnit + linter à chaque PR
│
├── .env.example
├── composer.json
└── README.md
```

---

## 3. Convention de nomenclature

- **Classes PHP** (`core/`, `orm/`, `backend/`) : `PascalCase.php`, un fichier = une classe, nom du fichier = nom de la classe.
- **Composants front** : dossier en `kebab-case` (ex. `livre-liste/`), contenant exactement 3 fichiers :
  `nom-composant.component.php`, `.component.html`, `.component.css`. Le `.html` ne contient **jamais** de `<!DOCTYPE>`, `<html>` ou `<body>` — uniquement le contenu qui irait dans le `<body>`. Le `.php` porte la logique et fait le lien avec le `.html` (include contrôlé, pas de mélange).
- **Repositories** : `NomEntiteRepository.php`, doit `extends AbstractRepository`.
- **Services** : `NomEntiteService.php`, contient la logique métier, jamais de SQL brut.
- **Controllers** : `NomEntiteController.php`, annoté `#[Controller]`, chaque méthode annotée `#[Route(...)]`.
- **Tables SQL** : `snake_case` pluriel (déjà fixé par le sujet : `livres`, `etudiants`, `emprunts`, `utilisateurs`).
- **Branches Git** : `feature/<equipe>-<description>` (ex. `feature/backend-emprunt-crud`, `feature/orm-repository-generique`, `feature/front-pagination`).
- **Commits** : Conventional Commits scopés — `feat(backend): ajoute EmpruntService`, `fix(orm): corrige findBy`, `test(brandon): tests LivreRepository`.

**Règle QA (Marc) :** aucun `SELECT`, `INSERT`, `UPDATE`, `DELETE` en dur en dehors de `orm/`. Tout dev backend qui écrit du SQL directement dans un Controller ou Service est bloqué en revue.

---

## 4. Qui dépend de qui (ordre des tâches)

C'est le point le plus important pour ne pas perdre de temps : **certaines briques bloquent tout le reste et doivent être livrées en premier.**

### Semaine 1 — Fondations (bloquantes, tout le monde attend ça)
1. **Marc** : crée le dépôt Git, la structure de dossiers ci-dessus, la base de données sur Render, le fichier `.env.example`, le squelette du `Router`, les annotations (`#[Controller]`, `#[Service]`, `#[Route]`) et le middleware d'auth (au moins l'interface).
2. **ADAMOU** : `Database.php` (connexion PDO unique vers Render), `AbstractEntity`, `AbstractRepository` avec `save()`, `find($id)`, `findBy()`, `findAll()`, `update()`, `delete()`, `paginate()`.
3. **Brandon** (en parallèle, sans dépendance) : configure `phpunit.xml` et le squelette `.github/workflows/ci.yml`.
4. **BARESSEY** (en parallèle, sans dépendance) : maquette `layout` (menu, header, footer), CSS de base, en attendant la commande CLI `make:component` de Marc.

➡️ **Rien de solide côté backend ne peut commencer tant que `AbstractRepository` et le middleware d'auth ne sont pas livrés.**

### Semaine 2 — Développement métier
5. **LeBonheur** : Models → Repositories spécifiques (`extends AbstractRepository`) → Services (logique métier : refuser un emprunt si quantité = 0, calcul du statut « en retard », etc.) → Controllers (Auth, Livre, Etudiant, Emprunt).
6. **BARESSEY** : composants CRUD (listes, formulaires ajout/modif), barre de recherche, pagination — branchés au fur et à mesure que LeBonheur expose les endpoints.
7. **Brandon** : commence les tests unitaires dès que `AbstractRepository` et les premiers `Service` existent.
8. **Marc** : revue continue au fil de l'eau (pas d'attente en fin de sprint).

### Semaine 3 — Intégration, sécurité, finitions
9. Recherche multicritère, pagination complète, exercices complémentaires 1 à 5 du sujet (recherche multicritère étudiants, livres disponibles, emprunts en retard, total livres empruntés, blocage emprunt si quantité 0).
10. Bonus si le temps le permet (voir ci-dessous).
11. CI GitHub Actions tourne sur chaque PR (PHPUnit + qualité).
12. Revue finale QA, démo.

---

## 5. Deux ajouts suggérés

1. **Linter automatique dans la CI** (PHP_CodeSniffer ou PHPStan) en plus de ta revue manuelle : ça t'évite de tout vérifier à la main et impose les règles d'architecture (pas de SQL hors `orm/`, respect des annotations) dès la Pull Request, avant même que tu regardes le code.
2. **Un petit système de logs centralisé dans `core/`** (`core/Log/Logger.php`), utilisable par tous les Services : couvre directement le bonus « journalisation des actions » et te donne un historique pour debug pendant les 3 semaines.

---

## 6. Rappel sécurité (imposé par le sujet)

- Connexion PDO unique et centralisée (`orm/Connection/Database.php`).
- Requêtes préparées partout, jamais de concaténation SQL.
- `password_hash()` / `password_verify()` pour les mots de passe.
- Sessions pour protéger les pages (via `AuthMiddleware`).
- `htmlspecialchars()` + validation/filtrage à l'affichage et à la saisie.
- Credentials Render **jamais commités** — uniquement dans `.env`, chargé via `config/database.php`.