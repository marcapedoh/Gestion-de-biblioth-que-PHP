<?php

declare(strict_types=1);

/**
 * Petites fonctions de validation, sans classe, partagées par livre.php,
 * etudiant.php, emprunt.php et auth.php. Chaque fonction ajoute un message
 * dans $errors (passé par référence) uniquement si la règle échoue.
 */

function valider_requis(array $data, string $champ, array &$errors, ?string $label = null): void
{
    $valeur = $data[$champ] ?? '';
    if (trim((string) $valeur) === '') {
        $errors[$champ] = ($label ?? ucfirst($champ)) . ' est obligatoire.';
    }
}

function valider_email(array $data, string $champ, array &$errors): void
{
    $valeur = trim((string) ($data[$champ] ?? ''));
    if ($valeur !== '' && filter_var($valeur, FILTER_VALIDATE_EMAIL) === false) {
        $errors[$champ] = "L'adresse email est invalide.";
    }
}

function valider_entier_positif(array $data, string $champ, array &$errors, ?string $label = null): void
{
    $valeur = $data[$champ] ?? null;
    if ($valeur === null || $valeur === '' || !ctype_digit((string) $valeur)) {
        $errors[$champ] = ($label ?? ucfirst($champ)) . ' doit être un nombre entier positif.';
    }
}

function valider_annee(array $data, string $champ, array &$errors): void
{
    $valeur = $data[$champ] ?? null;
    $anneeActuelle = (int) date('Y');
    if ($valeur === null || $valeur === '' || !ctype_digit((string) $valeur)
        || (int) $valeur < 1000 || (int) $valeur > $anneeActuelle
    ) {
        $errors[$champ] = 'Année invalide.';
    }
}

function valider_longueur_max(array $data, string $champ, int $max, array &$errors, ?string $label = null): void
{
    $valeur = (string) ($data[$champ] ?? '');
    if (mb_strlen($valeur) > $max) {
        $errors[$champ] = ($label ?? ucfirst($champ)) . " ne doit pas dépasser {$max} caractères.";
    }
}

function valider_date(array $data, string $champ, array &$errors, ?string $label = null): void
{
    $valeur = (string) ($data[$champ] ?? '');
    $formatValide = $valeur !== '' && DateTime::createFromFormat('Y-m-d', $valeur) !== false;
    if (!$formatValide) {
        $errors[$champ] = ($label ?? ucfirst($champ)) . ' doit être une date valide (AAAA-MM-JJ).';
    }
}

/**
 * Nettoie une chaîne saisie avant stockage : trim + suppression des balises.
 * L'échappement à l'affichage (htmlspecialchars()) reste la responsabilité
 * des vues (frontend/), au moment du rendu HTML.
 */
function nettoyer_chaine(string $valeur): string
{
    return trim(strip_tags($valeur));
}
