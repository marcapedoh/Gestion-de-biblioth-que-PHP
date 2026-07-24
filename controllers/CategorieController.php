<?php
/**
 * controllers/CategorieController.php
 * Valide et traite les formulaires liés aux catégories de livres.
 */

require_once __DIR__ . '/../models/Categorie.php';

/**
 * Valide et filtre les données d'un formulaire catégorie.
 *
 * @return array [bool $valide, array $donnees, array $erreurs]
 */
function validerDonneesCategorie(array $post): array
{
    $erreurs = [];

    $nom         = trim(filter_var($post['nom'] ?? '', FILTER_UNSAFE_RAW));
    $description = trim(filter_var($post['description'] ?? '', FILTER_UNSAFE_RAW));

    if ($nom === '') {
        $erreurs[] = "Le nom de la catégorie est obligatoire.";
    }

    $donnees = compact('nom', 'description');

    return [count($erreurs) === 0, $donnees, $erreurs];
}

/**
 * Traite la soumission du formulaire d'ajout d'une catégorie.
 */
function traiterAjoutCategorie(array $post): array
{
    [$valide, $donnees, $erreurs] = validerDonneesCategorie($post);

    if (!$valide) {
        return [false, $erreurs];
    }

    try {
        ajouterCategorie($donnees['nom'], $donnees['description']);
    } catch (PDOException $e) {
        return [false, ["Cette catégorie existe déjà."]];
    }

    return [true, []];
}

/**
 * Traite la soumission du formulaire de modification d'une catégorie.
 */
function traiterModificationCategorie(int $id, array $post): array
{
    [$valide, $donnees, $erreurs] = validerDonneesCategorie($post);

    if (!$valide) {
        return [false, $erreurs];
    }

    try {
        modifierCategorie($id, $donnees['nom'], $donnees['description']);
    } catch (PDOException $e) {
        return [false, ["Cette catégorie existe déjà."]];
    }

    return [true, []];
}
