<?php

declare(strict_types=1);

namespace Core\Http;


class Response
{


    private mixed $content;


    private int $status;



    public function __construct(
        mixed $content = '',
        int $status = 200
    )
    {

        $this->content = $content;

        $this->status = $status;

    }



    public function send(): void
    {

        http_response_code($this->status);


        echo $this->content;

    }




    public static function json(array $data): self
    {

        header(
            'Content-Type: application/json'
        );


        return new self(
            json_encode($data),
            200
        );

    }



    public static function redirect(string $url): self
    {

        header(
            "Location: $url"
        );


        return new self('',302);

    }



}