<?php

declare(strict_types=1);

namespace Core\Http;



class Request
{

    private array $query;

    private array $body;


    private array $server;



    public function __construct()
    {

        $this->query = $_GET;

        $this->body = $_POST;

        $this->server = $_SERVER;

    }



    public static function capture(): self
    {

        return new self();

    }



    public function method(): string
    {

        return $this->server['REQUEST_METHOD'] ?? 'GET';

    }



    public function uri(): string
    {

        return parse_url(
            $this->server['REQUEST_URI'],
            PHP_URL_PATH
        );

    }



    public function get(string $key, mixed $default=null): mixed
    {

        return $this->query[$key] ?? $default;

    }



    public function post(string $key, mixed $default=null): mixed
    {

        return $this->body[$key] ?? $default;

    }



}