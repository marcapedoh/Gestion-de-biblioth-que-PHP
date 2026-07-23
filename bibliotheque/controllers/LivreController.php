<?php
/**
 * controllers/LivreController.php
 * Valide et traite les formulaires liés aux livres, y compris la catégorie
 * et l'upload de la couverture (Bonus).
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Livre.php';

/**
 * Valide et filtre les données d'un formulaire livre.
 *
 * @return array [bool $valide, array $donnees, array $erreurs]
 */
function validerDonneesLivre(array $post): array
{
    $erreurs = [];

    $titre       = trim(filter_var($post['titre'] ?? '', FILTER_UNSAFE_RAW));
    $auteur      = trim(filter_var($post['auteur'] ?? '', FILTER_UNSAFE_RAW));
    $isbn        = trim(filter_var($post['isbn'] ?? '', FILTER_UNSAFE_RAW));
    $annee       = filter_var($post['annee'] ?? '', FILTER_VALIDATE_INT);
    $quantite    = filter_var($post['quantite'] ?? '', FILTER_VALIDATE_INT);
    $categorieId = filter_var($post['categorie_id'] ?? '', FILTER_VALIDATE_INT);

    if ($titre === '') {
        $erreurs[] = "Le titre est obligatoire.";
    }
    if ($auteur === '') {
        $erreurs[] = "L'auteur est obligatoire.";
    }
    if ($annee === false) {
        $erreurs[] = "L'année doit être un nombre valide.";
        $annee = 0;
    }
    if ($quantite === false || $quantite < 0) {
        $erreurs[] = "La quantité doit être un nombre positif.";
        $quantite = 0;
    }
    if ($categorieId === false || $categorieId <= 0) {
        $categorieId = null; // Catégorie optionnelle
    }

    $donnees = compact('titre', 'auteur', 'isbn', 'annee', 'quantite', 'categorieId');

    return [count($erreurs) === 0, $donnees, $erreurs];
}

/**
 * Bonus : upload de la couverture du livre.
 * Vérifie le type MIME, l'extension et la taille, puis déplace le fichier
 * dans assets/uploads/couvertures/ avec un nom unique.
 *
 * @return array [bool $succes, string|null $nomFichier, string|null $erreur]
 */
function uploaderCouvertureLivre(array $fichier): array
{
    // Aucun fichier envoyé : ce n'est pas une erreur (champ optionnel)
    if (!isset($fichier['error']) || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
        return [true, null, null];
    }

    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        return [false, null, "Erreur lors de l'envoi de l'image."];
    }

    if ($fichier['size'] > COUVERTURE_TAILLE_MAX) {
        return [false, null, "L'image de couverture ne doit pas dépasser 2 Mo."];
    }

    $typeMime = mime_content_type($fichier['tmp_name']);

    if (!in_array($typeMime, COUVERTURE_TYPES_AUTORISES, true)) {
        return [false, null, "Format d'image non autorisé (formats acceptés : JPG, PNG, WEBP)."];
    }

    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    $extension = $extensions[$typeMime];

    if (!is_dir(CHEMIN_COUVERTURES)) {
        mkdir(CHEMIN_COUVERTURES, 0775, true);
    }

    $nomFichier = 'couverture_' . uniqid() . '.' . $extension;
    $cheminDestination = CHEMIN_COUVERTURES . $nomFichier;

    if (!move_uploaded_file($fichier['tmp_name'], $cheminDestination)) {
        return [false, null, "Impossible d'enregistrer l'image de couverture."];
    }

    return [true, $nomFichier, null];
}

/**
 * Supprime physiquement un fichier de couverture (utilisé lors du
 * remplacement d'une couverture ou de la suppression d'un livre).
 */
function supprimerFichierCouverture(?string $nomFichier): void
{
    if ($nomFichier === null || $nomFichier === '') {
        return;
    }

    $chemin = CHEMIN_COUVERTURES . $nomFichier;

    if (is_file($chemin)) {
        @unlink($chemin);
    }
}

/**
 * Traite la soumission du formulaire d'ajout d'un livre.
 *
 * @return array [bool $succes, array $erreurs]
 */
function traiterAjoutLivre(array $post, array $files = []): array
{
    [$valide, $donnees, $erreurs] = validerDonneesLivre($post);

    [$uploadOk, $nomFichier, $erreurUpload] = uploaderCouvertureLivre($files['couverture'] ?? []);

    if (!$uploadOk) {
        $erreurs[] = $erreurUpload;
        $valide = false;
    }

    if (!$valide) {
        return [false, $erreurs];
    }

    ajouterLivre(
        $donnees['titre'],
        $donnees['auteur'],
        $donnees['isbn'],
        $donnees['annee'],
        $donnees['quantite'],
        $donnees['categorieId'],
        $nomFichier
    );

    return [true, []];
}

/**
 * Traite la soumission du formulaire de modification d'un livre.
 * Si aucune nouvelle image n'est envoyée, la couverture existante est conservée.
 *
 * @return array [bool $succes, array $erreurs]
 */
function traiterModificationLivre(int $id, array $post, array $files, ?string $couvertureActuelle): array
{
    [$valide, $donnees, $erreurs] = validerDonneesLivre($post);

    [$uploadOk, $nomFichier, $erreurUpload] = uploaderCouvertureLivre($files['couverture'] ?? []);

    if (!$uploadOk) {
        $erreurs[] = $erreurUpload;
        $valide = false;
    }

    if (!$valide) {
        return [false, $erreurs];
    }

    // Suppression manuelle de la couverture existante demandée par l'utilisateur
    $supprimerCouverture = isset($post['supprimer_couverture']) && $post['supprimer_couverture'] === '1';

    if ($nomFichier !== null) {
        // Nouvelle image envoyée : on supprime l'ancienne et on utilise la nouvelle
        supprimerFichierCouverture($couvertureActuelle);
        $couvertureFinale = $nomFichier;
    } elseif ($supprimerCouverture) {
        supprimerFichierCouverture($couvertureActuelle);
        $couvertureFinale = null;
    } else {
        $couvertureFinale = $couvertureActuelle;
    }

    modifierLivre(
        $id,
        $donnees['titre'],
        $donnees['auteur'],
        $donnees['isbn'],
        $donnees['annee'],
        $donnees['quantite'],
        $donnees['categorieId'],
        $couvertureFinale
    );

    return [true, []];
}
