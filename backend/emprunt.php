<?php

declare(strict_types=1);

use Orm\Connection\Database;

require_once __DIR__ . '/support/validator.php';
require_once __DIR__ . '/livre.php';
require_once __DIR__ . '/etudiant.php';

/**
 * Fonctions métier "emprunts".
 *
 * Le stock des livres est géré par les triggers PostgreSQL
 * trg_emprunts_before_insert / trg_emprunts_after_update
 * (voir orm/Migrations/003_functions_and_triggers.sql) : on ne décrémente
 * / réincrémente JAMAIS la quantité ici, la base s'en charge.
 *
 * Exercice complémentaire 5 (refus si quantité = 0) : double filet de
 * sécurité — 1) le contrôle PHP ci-dessous, qui renvoie un message clair
 * avant même de tenter l'INSERT ; 2) le trigger, qui refuserait quand
 * même l'INSERT (RAISE EXCEPTION -> PDOException) si ce contrôle était
 * contourné.
 */

const EMPRUNT_DUREE_JOURS = 14;

/**
 * Liste paginée des emprunts, avec recherche optionnelle (nom/prénom
 * étudiant, titre du livre, ou statut). Exigence de base du sujet
 * ("Rechercher un emprunt", section Gestion des emprunts) — pas un
 * exercice complémentaire.
 *
 * Jointure faite en SQL brut ici : le QueryBuilder (orm/) ne supporte pas
 * les JOIN, et emprunts n'a pas de sens à afficher sans le titre du livre
 * ni le nom de l'étudiant.
 */
function emprunt_lister(int $page = 1, int $perPage = 10, ?string $recherche = null): array
{
    $page = max(1, $page);
    $offset = ($page - 1) * $perPage;
    $recherche = trim((string) $recherche);

    $pdo = Database::getInstance();

    $where = '';
    $params = [];
    if ($recherche !== '') {
        $where = 'WHERE l.titre ILIKE :terme OR et.nom ILIKE :terme OR et.prenom ILIKE :terme OR e.statut ILIKE :terme';
        $params['terme'] = "%{$recherche}%";
    }

    $stmtTotal = $pdo->prepare(
        "SELECT COUNT(*) FROM emprunts e
         JOIN livres l ON l.id = e.id_livre
         JOIN etudiants et ON et.id = e.id_etudiant
         {$where}"
    );
    $stmtTotal->execute($params);
    $total = (int) $stmtTotal->fetchColumn();

    $stmt = $pdo->prepare(
        "SELECT e.*, l.titre, l.auteur, et.nom, et.prenom
         FROM emprunts e
         JOIN livres l ON l.id = e.id_livre
         JOIN etudiants et ON et.id = e.id_etudiant
         {$where}
         ORDER BY e.date_emprunt DESC
         LIMIT :limite OFFSET :decalage"
    );
    foreach ($params as $cle => $valeur) {
        $stmt->bindValue($cle, $valeur);
    }
    $stmt->bindValue('limite', $perPage, PDO::PARAM_INT);
    $stmt->bindValue('decalage', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'data'        => $stmt->fetchAll(),
        'total'       => $total,
        'page'        => $page,
        'per_page'    => $perPage,
        'total_pages' => (int) ceil($total / max(1, $perPage)),
    ];
}

/** Exercice complémentaire 3. */
function emprunt_en_retard(): array
{
    $pdo = Database::getInstance();

    return $pdo->query('SELECT * FROM fn_emprunts_en_retard()')->fetchAll();
}

/** Exercice complémentaire 4. */
function emprunt_total_livres_empruntes(): int
{
    $pdo = Database::getInstance();

    return (int) $pdo->query('SELECT fn_total_livres_empruntes()')->fetchColumn();
}

/**
 * @param array<string,mixed> $data Attendu : id_livre, id_etudiant, date_retour_prevue (optionnelle)
 * @return array{success:bool, errors:array<string,string>, message:?string}
 */
function emprunt_enregistrer(array $data): array
{
    $errors = [];
    valider_entier_positif($data, 'id_livre', $errors, 'Le livre');
    valider_entier_positif($data, 'id_etudiant', $errors, "L'étudiant");
    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors, 'message' => null];
    }

    $idLivre = (int) $data['id_livre'];
    $idEtudiant = (int) $data['id_etudiant'];

    $livre = livre_trouver($idLivre);
    if ($livre === null) {
        return ['success' => false, 'errors' => [], 'message' => 'Ce livre est introuvable.'];
    }

    $etudiant = etudiant_trouver($idEtudiant);
    if ($etudiant === null) {
        return ['success' => false, 'errors' => [], 'message' => 'Cet étudiant est introuvable.'];
    }

    // Exercice complémentaire 5 : refuser l'emprunt si stock épuisé.
    if ((int) $livre['quantite'] <= 0) {
        return [
            'success' => false,
            'errors'  => [],
            'message' => "Emprunt refusé : \"{$livre['titre']}\" n'est plus disponible (quantité = 0).",
        ];
    }

    $dateRetourPrevue = trim((string) ($data['date_retour_prevue'] ?? ''));
    if ($dateRetourPrevue === '') {
        $dateRetourPrevue = date('Y-m-d', strtotime('+' . EMPRUNT_DUREE_JOURS . ' days'));
    } else {
        $erreursDate = [];
        valider_date(['date_retour_prevue' => $dateRetourPrevue], 'date_retour_prevue', $erreursDate);
        if ($erreursDate !== []) {
            return ['success' => false, 'errors' => $erreursDate, 'message' => null];
        }
    }

    $pdo = Database::getInstance();

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO emprunts (id_livre, id_etudiant, date_emprunt, date_retour_prevue, statut)
             VALUES (:id_livre, :id_etudiant, :date_emprunt, :date_retour_prevue, :statut)'
        );
        $stmt->execute([
            'id_livre'           => $idLivre,
            'id_etudiant'        => $idEtudiant,
            'date_emprunt'       => date('Y-m-d'),
            'date_retour_prevue' => $dateRetourPrevue,
            'statut'             => 'En cours',
        ]);
    } catch (PDOException $e) {
        // Filet de sécurité : le trigger PostgreSQL a refusé l'INSERT
        // (stock probablement épuisé entre-temps par un autre utilisateur).
        return [
            'success' => false,
            'errors'  => [],
            'message' => 'Emprunt refusé par la base de données (stock épuisé entre-temps).',
        ];
    }

    return ['success' => true, 'errors' => [], 'message' => null];
}

/** @return array{success:bool, message:?string} */
function emprunt_retourner(int $id): array
{
    $pdo = Database::getInstance();

    $stmt = $pdo->prepare('SELECT * FROM emprunts WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $emprunt = $stmt->fetch();

    if ($emprunt === false) {
        return ['success' => false, 'message' => 'Emprunt introuvable.'];
    }

    if ($emprunt['statut'] === 'Retourné') {
        return ['success' => false, 'message' => 'Ce livre a déjà été retourné.'];
    }

    $stmt = $pdo->prepare(
        "UPDATE emprunts SET statut = 'Retourné', date_retour = :date_retour WHERE id = :id"
    );
    $stmt->execute(['date_retour' => date('Y-m-d'), 'id' => $id]);

    return ['success' => true, 'message' => null];
}

function emprunt_supprimer(int $id): bool
{
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('DELETE FROM emprunts WHERE id = :id');

    return $stmt->execute(['id' => $id]);
}
