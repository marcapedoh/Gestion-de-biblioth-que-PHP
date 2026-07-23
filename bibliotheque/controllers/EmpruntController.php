<?php
/**
 * controllers/EmpruntController.php
 * Valide et traite les formulaires liés aux emprunts.
 */

require_once __DIR__ . '/../models/Emprunt.php';

/**
 * Traite la soumission du formulaire d'enregistrement d'un emprunt.
 *
 * @return array [bool $succes, array $erreurs]
 */
function traiterAjoutEmprunt(array $post): array
{
    $erreurs = [];

    $idLivre          = filter_var($post['id_livre'] ?? '', FILTER_VALIDATE_INT);
    $idEtudiant       = filter_var($post['id_etudiant'] ?? '', FILTER_VALIDATE_INT);
    $dateEmprunt      = trim($post['date_emprunt'] ?? '');
    $dateRetourPrevue = trim($post['date_retour_prevue'] ?? '');

    if (!$idLivre) {
        $erreurs[] = "Veuillez sélectionner un livre.";
    }
    if (!$idEtudiant) {
        $erreurs[] = "Veuillez sélectionner un étudiant.";
    }
    if ($dateEmprunt === '' || !strtotime($dateEmprunt)) {
        $erreurs[] = "La date d'emprunt est invalide.";
    }
    if ($dateRetourPrevue === '' || !strtotime($dateRetourPrevue)) {
        $erreurs[] = "La date de retour prévue est invalide.";
    }

    if (count($erreurs) > 0) {
        return [false, $erreurs];
    }

    $resultat = enregistrerEmprunt($idLivre, $idEtudiant, $dateEmprunt, $dateRetourPrevue);

    if ($resultat !== true) {
        return [false, [$resultat]];
    }

    return [true, []];
}
