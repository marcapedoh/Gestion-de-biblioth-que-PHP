<?php

declare(strict_types=1);

use Core\Http\Request;
use Core\Http\Response;

require_once __DIR__ . '/../livre.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../support/vue.php';

/**
 * Fonctions de route "livres". Chaque fonction : reçoit une Request,
 * renvoie une Response (contrat imposé par Core\Http\Router::dispatch()).
 * À enregistrer sur le Router — voir backend/routes.php.
 */

function livre_liste(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    $page = max(1, (int) $request->get('page', 1));
    $recherche = trim((string) $request->get('q', ''));

    $resultat = livre_lister($page, 10, $recherche !== '' ? $recherche : null);

    return vue('livre-liste', [
        'livres'     => $resultat['data'],
        'pagination' => $resultat,
        'recherche'  => $recherche,
    ]);
}

function livre_afficher_formulaire_ajout(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    return vue('livre-form', ['livre' => null, 'errors' => []]);
}

function livre_ajouter(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    $data = [
        'titre'    => $request->post('titre'),
        'auteur'   => $request->post('auteur'),
        'isbn'     => $request->post('isbn'),
        'annee'    => $request->post('annee'),
        'quantite' => $request->post('quantite'),
    ];

    $resultat = livre_creer($data);
    if (!$resultat['success']) {
        return vue('livre-form', ['livre' => null, 'errors' => $resultat['errors'], 'old' => $data]);
    }

    return Response::redirect('/livres');
}

function livre_afficher_formulaire_modification(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    $id = (int) $request->get('id', 0);
    $livre = livre_trouver($id);

    if ($livre === null) {
        return Response::redirect('/livres');
    }

    return vue('livre-form', ['livre' => $livre, 'errors' => []]);
}

function livre_modifier_route(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    $id = (int) $request->post('id', 0);
    $data = [
        'titre'    => $request->post('titre'),
        'auteur'   => $request->post('auteur'),
        'isbn'     => $request->post('isbn'),
        'annee'    => $request->post('annee'),
        'quantite' => $request->post('quantite'),
    ];

    $resultat = livre_modifier($id, $data);
    if (!$resultat['success']) {
        return vue('livre-form', ['livre' => livre_trouver($id), 'errors' => $resultat['errors'], 'old' => $data]);
    }

    return Response::redirect('/livres');
}

function livre_supprimer_route(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    livre_supprimer((int) $request->post('id', 0));

    return Response::redirect('/livres');
}
