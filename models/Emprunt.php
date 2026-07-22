<?php

/**
 * models/Emprunt.php
 *
 * Toutes les opérations sur la table "emprunts", en fonctions PHP
 * simples (pas de classe).
 */

/**
 * Enregistre un nouvel emprunt.
 * Exercice 5 : refuse l'emprunt si la quantité disponible du livre est 0.
 * Retourne l'id de l'emprunt créé, ou false si refusé (stock épuisé).
 */
function enregistrerEmprunt(PDO $pdo, int $idLivre, int $idEtudiant, string $dateEmprunt, string $dateRetourPrevue): int|false
{
    $livre = trouverLivreParId($pdo, $idLivre);

    if ($livre === null || (int) $livre['quantite'] <= 0) {
        return false; // stock épuisé : on refuse l'emprunt
    }

    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO emprunts (id_livre, id_etudiant, date_emprunt, date_retour_prevue, statut)
             VALUES (:id_livre, :id_etudiant, :date_emprunt, :date_retour_prevue, 'En cours')"
        );
        $stmt->execute([
            'id_livre'           => $idLivre,
            'id_etudiant'        => $idEtudiant,
            'date_emprunt'       => $dateEmprunt,
            'date_retour_prevue' => $dateRetourPrevue,
        ]);

        $idEmprunt = (int) $pdo->lastInsertId('emprunts_id_seq');

        // On décrémente le stock du livre
        $pdo->prepare("UPDATE livres SET quantite = quantite - 1 WHERE id = :id")
            ->execute(['id' => $idLivre]);

        $pdo->commit();

        return $idEmprunt;
    } catch (PDOException $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Enregistre le retour d'un livre : met à jour le statut de l'emprunt
 * ET réincrémente le stock du livre correspondant.
 */
function retournerLivre(PDO $pdo, int $idEmprunt, ?string $dateRetour = null): bool
{
    $dateRetour = $dateRetour ?? date('Y-m-d');

    $emprunt = trouverEmpruntParId($pdo, $idEmprunt);
    if ($emprunt === null || $emprunt['statut'] !== 'En cours') {
        return false; // déjà retourné, ou emprunt introuvable
    }

    $pdo->beginTransaction();

    try {
        $pdo->prepare(
            "UPDATE emprunts SET statut = 'Retourné', date_retour = :date_retour WHERE id = :id"
        )->execute(['date_retour' => $dateRetour, 'id' => $idEmprunt]);

        $pdo->prepare("UPDATE livres SET quantite = quantite + 1 WHERE id = :id")
            ->execute(['id' => $emprunt['id_livre']]);

        $pdo->commit();

        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Retourne un emprunt par son id, ou null si introuvable.
 */
function trouverEmpruntParId(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM emprunts WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $emprunt = $stmt->fetch();

    return $emprunt ?: null;
}

/**
 * Liste tous les emprunts avec le titre du livre et le nom de l'étudiant
 * (jointure), pour un affichage direct dans les vues.
 */
function listerEmprunts(PDO $pdo): array
{
    $sql = "SELECT e.*, l.titre AS titre_livre, et.nom AS nom_etudiant, et.prenom AS prenom_etudiant
            FROM emprunts e
            JOIN livres l     ON l.id = e.id_livre
            JOIN etudiants et ON et.id = e.id_etudiant
            ORDER BY e.date_emprunt DESC";

    return $pdo->query($sql)->fetchAll();
}

/**
 * Recherche d'emprunts (par nom d'étudiant ou titre de livre).
 */
function rechercherEmprunts(PDO $pdo, string $terme): array
{
    $sql = "SELECT e.*, l.titre AS titre_livre, et.nom AS nom_etudiant, et.prenom AS prenom_etudiant
            FROM emprunts e
            JOIN livres l     ON l.id = e.id_livre
            JOIN etudiants et ON et.id = e.id_etudiant
            WHERE l.titre ILIKE :terme OR et.nom ILIKE :terme OR et.prenom ILIKE :terme
            ORDER BY e.date_emprunt DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['terme' => "%{$terme}%"]);

    return $stmt->fetchAll();
}

/**
 * Exercice 3 : liste des emprunts en retard (statut "En cours" et date
 * de retour prévue dépassée).
 */
function listerEmpruntsEnRetard(PDO $pdo): array
{
    $sql = "SELECT e.*, l.titre AS titre_livre, et.nom AS nom_etudiant, et.prenom AS prenom_etudiant,
                   (CURRENT_DATE - e.date_retour_prevue) AS jours_de_retard
            FROM emprunts e
            JOIN livres l     ON l.id = e.id_livre
            JOIN etudiants et ON et.id = e.id_etudiant
            WHERE e.statut = 'En cours' AND e.date_retour_prevue < CURRENT_DATE
            ORDER BY jours_de_retard DESC";

    return $pdo->query($sql)->fetchAll();
}

/**
 * Exercice 4 : nombre total de livres actuellement empruntés.
 */
function totalLivresEmpruntes(PDO $pdo): int
{
    return (int) $pdo->query("SELECT COUNT(*) FROM emprunts WHERE statut = 'En cours'")->fetchColumn();
}

/**
 * Pagination des emprunts : 10 résultats par page par défaut.
 */
function paginerEmprunts(PDO $pdo, int $page = 1, int $parPage = 10): array
{
    $page = max(1, $page);
    $offset = ($page - 1) * $parPage;

    $sql = "SELECT e.*, l.titre AS titre_livre, et.nom AS nom_etudiant, et.prenom AS prenom_etudiant
            FROM emprunts e
            JOIN livres l     ON l.id = e.id_livre
            JOIN etudiants et ON et.id = e.id_etudiant
            ORDER BY e.date_emprunt DESC
            LIMIT :limite OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $total = (int) $pdo->query("SELECT COUNT(*) FROM emprunts")->fetchColumn();

    return [
        'donnees'     => $stmt->fetchAll(),
        'total'       => $total,
        'page'        => $page,
        'par_page'    => $parPage,
        'total_pages' => (int) ceil($total / max(1, $parPage)),
    ];
}
