<?php

declare(strict_types=1);

use Core\Http\Response;

/**
 * Inclut un composant frontend/components/{composant}/{composant}.component.php
 * en lui passant $data (extrait comme variables), capture le HTML produit et
 * le renvoie encapsulé dans une Response — c'est ce que Router::dispatch()
 * attend en retour de chaque fonction de contrôleur.
 */
function vue(string $composant, array $data = []): Response
{
    extract($data);

    $chemin = dirname(__DIR__, 2) . "/frontend/components/{$composant}/{$composant}.component.php";

    ob_start();
    require $chemin;
    $html = (string) ob_get_clean();

    return new Response($html);
}
