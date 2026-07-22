<?php

declare(strict_types=1);

use Core\Http\Request;
use Core\Http\Response;

require_once __DIR__ . '/../etudiant.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../support/vue.php';

function etudiant_liste(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    $page = max(1, (int) $request->get('page', 1));
    $recherche = trim((string) $request->get('q', ''));

    $resultat = etudiant_lister($page, 10, $recherche !== '' ? $recherche : null);

    return vue('etudiant-liste', [
        'etudiants'  => $resultat['data'],
        'pagination' => $resultat,
        'recherche'  => $recherche,
    ]);
}

function etudiant_afficher_formulaire_ajout(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    return vue('etudiant-form', ['etudiant' => null, 'errors' => []]);
}

function etudiant_ajouter(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    $data = [
        'nom'       => $request->post('nom'),
        'prenom'    => $request->post('prenom'),
        'email'     => $request->post('email'),
        'telephone' => $request->post('telephone'),
        'filiere'   => $request->post('filiere'),
    ];

    $resultat = etudiant_creer($data);
    if (!$resultat['success']) {
        return vue('etudiant-form', ['etudiant' => null, 'errors' => $resultat['errors'], 'old' => $data]);
    }

    return Response::redirect('/etudiants');
}

function etudiant_afficher_formulaire_modification(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    $id = (int) $request->get('id', 0);
    $etudiant = etudiant_trouver($id);

    if ($etudiant === null) {
        return Response::redirect('/etudiants');
    }

    return vue('etudiant-form', ['etudiant' => $etudiant, 'errors' => []]);
}

function etudiant_modifier_route(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    $id = (int) $request->post('id', 0);
    $data = [
        'nom'       => $request->post('nom'),
        'prenom'    => $request->post('prenom'),
        'email'     => $request->post('email'),
        'telephone' => $request->post('telephone'),
        'filiere'   => $request->post('filiere'),
    ];

    $resultat = etudiant_modifier($id, $data);
    if (!$resultat['success']) {
        return vue('etudiant-form', ['etudiant' => etudiant_trouver($id), 'errors' => $resultat['errors'], 'old' => $data]);
    }

    return Response::redirect('/etudiants');
}

function etudiant_supprimer_route(Request $request): Response
{
    if (!auth_est_connecte()) {
        return Response::redirect('/login');
    }

    etudiant_supprimer((int) $request->post('id', 0));

    return Response::redirect('/etudiants');
}
