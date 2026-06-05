<?php

namespace App\Controllers;

use App\Models\Categoria;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoriaController
{
    public function listar(Request $request, Response $response): Response
    {
        $categorias = Categoria::orderBy('nombre')->get();

        $response->getBody()->write(json_encode([
            'success'    => true,
            'categorias' => $categorias->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
