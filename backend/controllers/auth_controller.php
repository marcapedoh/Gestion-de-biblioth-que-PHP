<?php

declare(strict_types=1);

use Core\Http\Request;
use Core\Http\Response;

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../support/vue.php';

function auth_afficher_connexion(Request $request): Response
{
    if (auth_est_connecte()) {
        return Response::redirect('/livres');
    }

    return vue('connexion', ['errors' => []]);
}

function auth_connecter_route(Request $request): Response
{
    $resultat = auth_connecter([
        'email'        => $request->post('email'),
        'mot_de_passe' => $request->post('mot_de_passe'),
    ]);

    if (!$resultat['success']) {
        return vue('connexion', ['errors' => $resultat['errors']]);
    }

    return Response::redirect('/livres');
}

function auth_deconnecter_route(Request $request): Response
{
    auth_deconnecter();

    return Response::redirect('/login');
}
