<?php

declare(strict_types=1);

use Core\Http\Request;
use Core\Http\Response;

require_once __DIR__ . '/../emprunt.php';
require_once __DIR__ . '/../livre.php';
require_once __DIR__ . '/../etudiant.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../support/vue.php';

function emprunt_liste(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    $page = max(1, (int) $request->get('page', 1));
    $recherche = trim((string) $request->get('q', ''));

    $resultat = emprunt_lister($page, 10, $recherche !== '' ? $recherche : null);

    return vue('emprunt-liste', [
        'emprunts'   => $resultat['data'],
        'pagination' => $resultat,
        'recherche'  => $recherche,
        'modeRetard' => false,
    ]);
}

/** Exercice complémentaire 3. */
function emprunt_liste_retard(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    return vue('emprunt-liste', [
        'emprunts'   => emprunt_en_retard(),
        'pagination' => null,
        'recherche'  => '',
        'modeRetard' => true,
    ]);
}

function emprunt_afficher_formulaire_ajout(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    return vue('emprunt-form', [
        'livresDisponibles' => livre_disponibles(),
        'etudiants'         => etudiant_tous(),
        'errors'            => [],
    ]);
}

function emprunt_ajouter(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    $data = [
        'id_livre'           => $request->post('id_livre'),
        'id_etudiant'        => $request->post('id_etudiant'),
        'date_retour_prevue' => $request->post('date_retour_prevue'),
    ];

    $resultat = emprunt_enregistrer($data);
    if (!$resultat['success']) {
        $errors = $resultat['errors'];
        if ($resultat['message'] !== null) {
            $errors['general'] = $resultat['message'];
        }

        return vue('emprunt-form', [
            'livresDisponibles' => livre_disponibles(),
            'etudiants'         => etudiant_tous(),
            'errors'            => $errors,
        ]);
    }

    return Response::redirect('/emprunts');
}

function emprunt_retourner_route(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    // Le message d'erreur éventuel (déjà retourné...) n'est pas encore
    // affichable : il n'y a pas de système de messages flash dans core/
    // pour l'instant. À brancher ici dès qu'il existera.
    emprunt_retourner((int) $request->post('id', 0));

    return Response::redirect('/emprunts');
}

function emprunt_supprimer_route(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    emprunt_supprimer((int) $request->post('id', 0));

    return Response::redirect('/emprunts');
}
