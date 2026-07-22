<?php

declare(strict_types=1);

use Core\Http\Router;

require_once __DIR__ . '/controllers/livre_controller.php';
require_once __DIR__ . '/controllers/etudiant_controller.php';
require_once __DIR__ . '/controllers/emprunt_controller.php';
require_once __DIR__ . '/controllers/auth_controller.php';

/**
 * Enregistre toutes les routes du backend sur un Router.
 *
 * ⚠️ À COORDONNER AVEC MARC : Core\Kernel::run() crée aujourd'hui son
 * propre Router (vide, aucune route enregistrée) sans possibilité d'en
 * injecter un autre. Il faudra que Kernel accepte un Router externe, ou
 * expose le sien, pour que ce fichier puisse être branché. En attendant,
 * exemple d'utilisation prévue dans public/index.php :
 *
 *   $router = new Core\Http\Router();
 *   (require __DIR__ . '/../backend/routes.php')($router);
 *   (new Core\Http\Response(...))->send(...); // ou adapter Kernel
 */
return function (Router $router): void {
    $router->get('/livres', 'livre_liste');
    $router->get('/livres/ajouter', 'livre_afficher_formulaire_ajout');
    $router->post('/livres/ajouter', 'livre_ajouter');
    $router->get('/livres/modifier', 'livre_afficher_formulaire_modification');
    $router->post('/livres/modifier', 'livre_modifier_route');
    $router->post('/livres/supprimer', 'livre_supprimer_route');

    $router->get('/etudiants', 'etudiant_liste');
    $router->get('/etudiants/ajouter', 'etudiant_afficher_formulaire_ajout');
    $router->post('/etudiants/ajouter', 'etudiant_ajouter');
    $router->get('/etudiants/modifier', 'etudiant_afficher_formulaire_modification');
    $router->post('/etudiants/modifier', 'etudiant_modifier_route');
    $router->post('/etudiants/supprimer', 'etudiant_supprimer_route');

    $router->get('/emprunts', 'emprunt_liste');
    $router->get('/emprunts/retard', 'emprunt_liste_retard');
    $router->get('/emprunts/ajouter', 'emprunt_afficher_formulaire_ajout');
    $router->post('/emprunts/ajouter', 'emprunt_ajouter');
    $router->post('/emprunts/retourner', 'emprunt_retourner_route');
    $router->post('/emprunts/supprimer', 'emprunt_supprimer_route');

    $router->get('/login', 'auth_afficher_connexion');
    $router->post('/login', 'auth_connecter_route');
    $router->get('/logout', 'auth_deconnecter_route');
};
