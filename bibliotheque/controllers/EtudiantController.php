<?php
/**
 * controllers/EtudiantController.php
 * Valide et traite les formulaires liés aux étudiants.
 */

require_once __DIR__ . '/../models/Etudiant.php';

/**
 * Valide et filtre les données d'un formulaire étudiant.
 *
 * @return array [bool $valide, array $donnees, array $erreurs]
 */
function validerDonneesEtudiant(array $post): array
{
    $erreurs = [];

    $nom       = trim(filter_var($post['nom'] ?? '', FILTER_UNSAFE_RAW));
    $prenom    = trim(filter_var($post['prenom'] ?? '', FILTER_UNSAFE_RAW));
    $email     = trim(filter_var($post['email'] ?? '', FILTER_UNSAFE_RAW));
    $telephone = trim(filter_var($post['telephone'] ?? '', FILTER_UNSAFE_RAW));
    $filiere   = trim(filter_var($post['filiere'] ?? '', FILTER_UNSAFE_RAW));

    if ($nom === '') {
        $erreurs[] = "Le nom est obligatoire.";
    }
    if ($prenom === '') {
        $erreurs[] = "Le prénom est obligatoire.";
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'adresse email n'est pas valide.";
    }

    $donnees = compact('nom', 'prenom', 'email', 'telephone', 'filiere');

    return [count($erreurs) === 0, $donnees, $erreurs];
}

/**
 * Traite la soumission du formulaire d'ajout d'un étudiant.
 */
function traiterAjoutEtudiant(array $post): array
{
    [$valide, $donnees, $erreurs] = validerDonneesEtudiant($post);

    if (!$valide) {
        return [false, $erreurs];
    }

    ajouterEtudiant($donnees['nom'], $donnees['prenom'], $donnees['email'], $donnees['telephone'], $donnees['filiere']);
    return [true, []];
}

/**
 * Traite la soumission du formulaire de modification d'un étudiant.
 */
function traiterModificationEtudiant(int $id, array $post): array
{
    [$valide, $donnees, $erreurs] = validerDonneesEtudiant($post);

    if (!$valide) {
        return [false, $erreurs];
    }

    modifierEtudiant($id, $donnees['nom'], $donnees['prenom'], $donnees['email'], $donnees['telephone'], $donnees['filiere']);
    return [true, []];
}
