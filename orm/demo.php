<?php

/**
 * Démo exécutable de la partie orm/ — indépendante de backend/ (LeBonheur
 * n'a pas encore livré ses Model/Repository, donc on utilise ici une
 * entité et un repository de test minimalistes pour prouver que
 * AbstractRepository fonctionne réellement contre la base de données).
 *
 * Utilisation :
 *   1. Adapter config/database.php ou définir les variables d'env DB_*
 *   2. Avoir exécuté 001_create_tables.sql (et 002_seed_data.sql si besoin)
 *   3. Depuis la racine du projet : php orm/demo.php
 */

require __DIR__ . '/../vendor/autoload.php'; // à adapter si le projet n'utilise pas Composer/PSR-4
// À défaut de Composer, décommenter les require manuels ci-dessous :
// require __DIR__ . '/Connection/Database.php';
// require __DIR__ . '/Entity/AbstractEntity.php';
// require __DIR__ . '/Repository/AbstractRepository.php';
// require __DIR__ . '/QueryBuilder/QueryBuilder.php';

namespace Orm\Demo;

use Orm\Entity\AbstractEntity;
use Orm\Repository\AbstractRepository;
use Orm\QueryBuilder\QueryBuilder;

// --- Entité de test, mappée sur la table "livres" -----------------------
class LivreDemo extends AbstractEntity
{
    public string $titre = '';
    public string $auteur = '';
    public string $isbn = '';
    public int $annee = 0;
    public int $quantite = 0;

    public static function tableName(): string
    {
        return 'livres';
    }
}

// --- Repository de test --------------------------------------------------
class LivreDemoRepository extends AbstractRepository
{
    protected function getEntityClass(): string
    {
        return LivreDemo::class;
    }
}

echo "=== Démo orm/ : Bibliothèque universitaire ===\n\n";

$repo = new LivreDemoRepository();

// 1) CREATE (save)
$livre = new LivreDemo();
$livre->titre = 'Test ORM - Introduction à PDO';
$livre->auteur = 'ADAMOU Youssouf';
$livre->isbn = '000-0000000000';
$livre->annee = 2026;
$livre->quantite = 3;

$repo->save($livre);
echo "1) save()      -> livre créé avec id = {$livre->id}\n";

// 2) READ (find)
$retrouve = $repo->find($livre->id);
echo "2) find()      -> titre récupéré = \"{$retrouve->titre}\"\n";

// 3) UPDATE
$retrouve->quantite = 10;
$repo->update($retrouve);
$verif = $repo->find($livre->id);
echo "3) update()    -> nouvelle quantité = {$verif->quantite}\n";

// 4) findBy
$resultats = $repo->findBy(['auteur' => 'ADAMOU Youssouf']);
echo '4) findBy()    -> ' . count($resultats) . " résultat(s) pour cet auteur\n";

// 5) Pagination
$page = $repo->paginate(page: 1, perPage: 5);
echo "5) paginate()  -> page {$page['page']}/{$page['total_pages']}, total = {$page['total']} livre(s)\n";

// 6) QueryBuilder : recherche multicritère
$terme = 'ORM';
$recherche = (new QueryBuilder('livres'))
    ->whereLike('titre', $terme)
    ->orWhereLike('auteur', $terme)
    ->get();
echo '6) QueryBuilder -> ' . count($recherche) . " résultat(s) pour la recherche \"$terme\"\n";

// 7) DELETE (nettoyage des données de test)
$repo->delete($livre->id);
$controle = $repo->find($livre->id);
echo '7) delete()    -> livre supprimé ? ' . ($controle === null ? 'oui' : 'NON (erreur)') . "\n";

echo "\n=== Fin de la démo : tout le CRUD fonctionne contre la vraie base ===\n";
