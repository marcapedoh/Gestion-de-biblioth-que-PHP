<?php

declare(strict_types=1);

namespace Core;

use Core\Http\Request;
use Core\Http\Router;


class Kernel
{

    private Router $router;


    public function __construct()
    {
        $this->router = new Router();
    }



    public function run(): void
    {

        $request = Request::capture();


        $response = $this->router->dispatch($request);


        $response->send();

    }


}