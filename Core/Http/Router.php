<?php

declare(strict_types=1);

namespace Core\Http;



class Router
{


    private array $routes = [];




    public function get(
        string $uri,
        callable $action
    ): void
    {

        $this->routes['GET'][$uri] = $action;

    }





    public function post(
        string $uri,
        callable $action
    ): void
    {

        $this->routes['POST'][$uri] = $action;

    }






    public function dispatch(Request $request): Response
    {


        $method = $request->method();

        $uri = $request->uri();



        if(isset($this->routes[$method][$uri]))
        {

            return call_user_func(
                $this->routes[$method][$uri],
                $request
            );

        }



        return new Response(
            "404 Not Found",
            404
        );


    }



}