<?php

namespace App\Controllers;

use App\Models\Mesa;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MesaController
{
    private array $estados = ['disponible', 'reservada', 'ocupada', 'fuera_servicio'];

    public function listar(Request $request, Response $response): Response
    {
        $mesas = Mesa::orderBy('id')->get();

        $response->getBody()->write(json_encode([
            'success' => true,
            'mesas'   => $mesas->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function crear(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $numero = trim($body['numero'] ?? '');
        $capacidad = (int) ($body['capacidad'] ?? 0);
        $estado = $body['estado'] ?? 'disponible';

        if ($numero === '') {
            return $this->error($response, 'El número de mesa es requerido', 400);
        }

        if ($capacidad <= 0) {
            return $this->error($response, 'La capacidad debe ser mayor a cero', 400);
        }

        if (!in_array($estado, $this->estados, true)) {
            return $this->error($response, 'Estado de mesa inválido', 400);
        }

        if (Mesa::where('numero', $numero)->exists()) {
            return $this->error($response, 'Ya existe una mesa con ese número', 409);
        }

        $mesa = Mesa::create([
            'numero'    => $numero,
            'capacidad' => $capacidad,
            'estado'    => $estado,
        ]);

        $response->getBody()->write(json_encode([
            'success' => true,
            'mesa'    => $mesa->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function actualizar(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);

        if (!$mesa) {
            return $this->error($response, 'Mesa no encontrada', 404);
        }

        $body = $request->getParsedBody();

        if (isset($body['numero'])) {
            $numero = trim($body['numero']);
            if ($numero === '') {
                return $this->error($response, 'El número de mesa es requerido', 400);
            }
            if (Mesa::where('numero', $numero)->where('id', '!=', $mesa->id)->exists()) {
                return $this->error($response, 'Ya existe una mesa con ese número', 409);
            }
            $mesa->numero = $numero;
        }

        if (isset($body['capacidad'])) {
            $capacidad = (int) $body['capacidad'];
            if ($capacidad <= 0) {
                return $this->error($response, 'La capacidad debe ser mayor a cero', 400);
            }
            $mesa->capacidad = $capacidad;
        }

        if (isset($body['estado'])) {
            if (!in_array($body['estado'], $this->estados, true)) {
                return $this->error($response, 'Estado de mesa inválido', 400);
            }
            $mesa->estado = $body['estado'];
        }

        $mesa->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'mesa'    => $mesa->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);

        if (!$mesa) {
            return $this->error($response, 'Mesa no encontrada', 404);
        }

        $body = $request->getParsedBody();
        $estado = $body['estado'] ?? '';

        if (!in_array($estado, $this->estados, true)) {
            return $this->error($response, 'Estado de mesa inválido', 400);
        }

        $mesa->estado = $estado;
        $mesa->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'mesa'    => $mesa->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function eliminar(Request $request, Response $response, array $args): Response
{
    $mesa = Mesa::find($args['id']);

    if (!$mesa) {
        return $this->error($response, 'Mesa no encontrada', 404);
    }

    $mesa->delete();

    $response->getBody()->write(json_encode([
        'success' => true,
        'message' => 'Mesa eliminada correctamente',
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
}

    private function error(Response $response, string $message, int $status): Response
    {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $message,
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
