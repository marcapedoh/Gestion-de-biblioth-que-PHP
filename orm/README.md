# orm/ — BD & ORM (ADAMOU Youssouf)

## Contenu

| Fichier | Rôle |
|---|---|
| `Connection/Database.php` | Singleton PDO, connexion unique centralisée (lit `config/database.php`) |
| `Entity/AbstractEntity.php` | Classe mère de toutes les entités : hydratation + conversion en tableau |
| `Repository/AbstractRepository.php` | CRUD générique : `save()`, `find()`, `findBy()`, `findAll()`, `update()`, `delete()`, `paginate()`, `count()` |
| `QueryBuilder/QueryBuilder.php` | Requêtes SELECT complexes (recherche multicritère, filtres combinés) |
| `Migrations/001_create_tables.sql` | Création de la base + 4 tables du cahier des charges |

## Comment LeBonheur (Backend) doit utiliser ce dossier

### 1. Créer une entité (backend/Model/Livre.php)

```php
<?php
namespace Backend\Model;

use Orm\Entity\AbstractEntity;

class Livre extends AbstractEntity
{
    public string $titre = '';
    public string $auteur = '';
    public string $isbn = '';
    public int $annee = 0;
    public int $quantite = 0;
}
```

### 2. Créer le repository spécifique (backend/Repository/LivreRepository.php)

```php
<?php
namespace Backend\Repository;

use Orm\Repository\AbstractRepository;
use Backend\Model\Livre;

class LivreRepository extends AbstractRepository
{
    protected function getEntityClass(): string
    {
        return Livre::class;
    }

    // Méthodes spécifiques aux livres, si besoin (au-dessus du CRUD générique)
    public function disponibles(): array
    {
        return array_filter($this->findAll(), fn (Livre $l) => $l->quantite > 0);
    }
}
```

### 3. Utilisation dans un Service/Controller

```php
$repo = new LivreRepository();

$livre = new Livre();
$livre->titre = 'Le Petit Prince';
$livre->auteur = 'Antoine de Saint-Exupéry';
$livre->isbn = '978-2070408504';
$livre->annee = 1943;
$livre->quantite = 5;
$repo->save($livre); // INSERT, $livre->id est rempli automatiquement

$page = $repo->paginate(page: 1, perPage: 10); // Exercice pagination
```

### 4. Recherche multicritère (avec QueryBuilder, exemple pour les livres)

```php
use Orm\QueryBuilder\QueryBuilder;

$terme = $_GET['q'] ?? '';

$resultats = (new QueryBuilder('livres'))
    ->whereLike('titre', $terme)
    ->orWhereLike('auteur', $terme)
    ->orWhereLike('isbn', $terme)
    ->orderBy('titre')
    ->limit(10, 0)
    ->get();
```

### 5. Exercices complémentaires couverts par le QueryBuilder

```php
// Exercice 2 : livres disponibles
(new QueryBuilder('livres'))->where('quantite', '>', 0)->get();

// Exercice 3 : emprunts en retard
(new QueryBuilder('emprunts'))
    ->where('statut', '=', 'En cours')
    ->where('date_retour_prevue', '<', date('Y-m-d'))
    ->get();

// Exercice 4 : total livres empruntés
(new QueryBuilder('emprunts'))->where('statut', '=', 'En cours')->count();

// Exercice 5 : blocage emprunt si quantité = 0 -> logique métier
// dans backend/Service/EmpruntService.php (pas dans orm/), ex :
// if ($livre->quantite <= 0) { throw new \DomainException("Stock épuisé"); }
```

## Configuration requise (.env, géré par Marc/core)

⚠️ **Base de données : PostgreSQL** (pas MySQL, malgré le SQL du sujet — adapté ici).

```
DB_DRIVER=pgsql
DB_HOST=<host_render_ou_local>
DB_PORT=5432
DB_DATABASE=bibliotheque
DB_USERNAME=<user_postgres>
DB_PASSWORD=<password_postgres>
```

En local (pgAdmin), créer la base "bibliotheque" au préalable, puis exécuter
dans l'ordre, connecté sur cette base (Query Tool) :
`001_create_tables.sql` → `002_seed_data.sql` → `003_functions_and_triggers.sql`
→ `004_bonus_categories.sql` → `005_bonus_logs.sql`.

## Bonus couverts côté BD/ORM

Sur les 7 bonus du sujet, 3 touchent la base de données / l'ORM (les autres —
upload de couverture, export PDF/Excel, interface Bootstrap — sont côté
LeBonheur/BARESSEY) :

### 1. Catégories de livres (`004_bonus_categories.sql`)
Table `categories` + colonne `categorie_id` (nullable) sur `livres`.
LeBonheur n'a qu'à créer `backend/Model/Categorie.php` (même modèle que
`Livre.php`) et `CategorieRepository.php` pour en profiter.

### 2. Journalisation des actions (`005_bonus_logs.sql`)
Table `logs`, pensée pour `core/Log/Logger.php` (proposition de Marc).
Exemple d'utilisation dans un Service, via une entité/repository dédiés :

```php
// backend/Model/LogEntry.php (ou core/, selon ce que Marc préfère)
class LogEntry extends \Orm\Entity\AbstractEntity
{
    public ?int $utilisateurId = null;
    public string $action = '';
    public string $entite = '';
    public ?int $entiteId = null;
    public ?string $details = null;
}

class LogRepository extends \Orm\Repository\AbstractRepository
{
    protected function getEntityClass(): string { return LogEntry::class; }
}

// Dans un Service, après une action :
$log = new LogEntry();
$log->utilisateurId = $_SESSION['user_id'];
$log->action = 'suppression';
$log->entite = 'livre';
$log->entiteId = $livreId;
(new LogRepository())->save($log);
```

### 3. Tableau de bord (statistiques)
Aucune nouvelle table nécessaire : `AbstractRepository::count()` et
`QueryBuilder::count()` suffisent. Exemple pour un `DashboardService` :

```php
$stats = [
    'total_livres'          => (new LivreRepository())->count(),
    'total_etudiants'       => (new EtudiantRepository())->count(),
    'emprunts_en_cours'     => (new QueryBuilder('emprunts'))->where('statut', '=', 'En cours')->count(),
    'emprunts_en_retard'    => (new QueryBuilder('emprunts'))
        ->where('statut', '=', 'En cours')
        ->where('date_retour_prevue', '<', date('Y-m-d'))
        ->count(),
];
```

### 4. Tri des listes
Déjà géré nativement : `findAll($colonne, 'ASC'|'DESC')`,
`paginate(..., orderBy: $colonne)`, ou `QueryBuilder::orderBy()`.

## Notes pour la revue QA (Marc)

- Aucune requête SQL brute en dehors de `orm/` : tout passe par `AbstractRepository` ou `QueryBuilder`.
- Toutes les requêtes utilisent des paramètres liés (`:param`), jamais de concaténation de valeurs.
- Les noms de colonnes utilisés dans `ORDER BY`/tri sont validés par regex avant d'être insérés dans le SQL (impossible de les lier en PDO).
- `Database.php` est un singleton strict (constructeur et clonage privés).
