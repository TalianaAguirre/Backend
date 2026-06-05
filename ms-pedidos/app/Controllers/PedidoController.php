<?php

namespace App\Controllers;

use App\Models\DetallePedido;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PedidoController
{
    private array $estados = ['pendiente', 'en_preparacion', 'entregado', 'pagado', 'cancelado'];

    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query = Pedido::with('detalles');

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        $pedidos = $query->orderBy('id', 'desc')->get();

        $response->getBody()->write(json_encode([
            'success' => true,
            'pedidos' => $pedidos->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function ver(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::with('detalles')->find($args['id']);

        if (!$pedido) {
            return $this->error($response, 'Pedido no encontrado', 404);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'pedido'  => $pedido->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function crear(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $mesaId = (int) ($body['mesa_id'] ?? 0);
        $items = $body['productos'] ?? [];
        $estado = $body['estado'] ?? 'pendiente';

        if ($mesaId <= 0) {
            return $this->error($response, 'Debe seleccionar una mesa', 400);
        }

        if (!is_array($items) || count($items) === 0) {
            return $this->error($response, 'El pedido no puede estar vacío', 400);
        }

        if (!in_array($estado, $this->estados, true)) {
            return $this->error($response, 'Estado de pedido inválido', 400);
        }

        $mesa = Mesa::find($mesaId);
        if (!$mesa) {
            return $this->error($response, 'La mesa no existe', 404);
        }

        if ($mesa->estado === 'disponible') {
            return $this->error($response, 'No se puede registrar un pedido para una mesa disponible', 409);
        }

        $resultado = $this->construirLineas($items);
        if (isset($resultado['error'])) {
            return $this->error($response, $resultado['error'], $resultado['status']);
        }

        $pedido = null;
        Capsule::connection()->transaction(function () use (&$pedido, $mesaId, $estado, $resultado) {
            $pedido = Pedido::create([
                'mesa_id'  => $mesaId,
                'fecha'    => date('Y-m-d'),
                'hora'     => date('H:i:s'),
                'subtotal' => $resultado['subtotal'],
                'total'    => $resultado['subtotal'],
                'estado'   => $estado,
            ]);

            foreach ($resultado['lineas'] as $linea) {
                $pedido->detalles()->create($linea);
            }
        });

        $pedido->load('detalles');

        $response->getBody()->write(json_encode([
            'success' => true,
            'pedido'  => $pedido->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function actualizar(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {
            return $this->error($response, 'Pedido no encontrado', 404);
        }

        $body = $request->getParsedBody();
        $mesaId = (int) ($body['mesa_id'] ?? $pedido->mesa_id);
        $items = $body['productos'] ?? null;

        $mesa = Mesa::find($mesaId);
        if (!$mesa) {
            return $this->error($response, 'La mesa no existe', 404);
        }

        if ($mesa->estado === 'disponible') {
            return $this->error($response, 'No se puede registrar un pedido para una mesa disponible', 409);
        }

        $resultado = null;
        if ($items !== null) {
            if (!is_array($items) || count($items) === 0) {
                return $this->error($response, 'El pedido no puede estar vacío', 400);
            }

            $resultado = $this->construirLineas($items);
            if (isset($resultado['error'])) {
                return $this->error($response, $resultado['error'], $resultado['status']);
            }
        }

        if (isset($body['estado']) && !in_array($body['estado'], $this->estados, true)) {
            return $this->error($response, 'Estado de pedido inválido', 400);
        }

        Capsule::connection()->transaction(function () use ($pedido, $mesaId, $items, $resultado, $body) {
            $pedido->mesa_id = $mesaId;

            if ($resultado !== null) {
                $pedido->detalles()->delete();
                foreach ($resultado['lineas'] as $linea) {
                    $pedido->detalles()->create($linea);
                }
                $pedido->subtotal = $resultado['subtotal'];
                $pedido->total = $resultado['subtotal'];
            }

            if (isset($body['estado'])) {
                $pedido->estado = $body['estado'];
            }

            $pedido->save();
        });

        $pedido->load('detalles');

        $response->getBody()->write(json_encode([
            'success' => true,
            'pedido'  => $pedido->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {
            return $this->error($response, 'Pedido no encontrado', 404);
        }

        $body = $request->getParsedBody();
        $estado = $body['estado'] ?? '';

        if (!in_array($estado, $this->estados, true)) {
            return $this->error($response, 'Estado de pedido inválido', 400);
        }

        $pedido->estado = $estado;
        $pedido->save();
        $pedido->load('detalles');

        $response->getBody()->write(json_encode([
            'success' => true,
            'pedido'  => $pedido->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    private function construirLineas(array $items): array
    {
        $lineas = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $productoId = (int) ($item['producto_id'] ?? 0);
            $cantidad = (int) ($item['cantidad'] ?? 0);

            if ($cantidad < 1) {
                return ['error' => 'La cantidad debe ser al menos uno', 'status' => 400];
            }

            $producto = Producto::find($productoId);
            if (!$producto) {
                return ['error' => "El producto {$productoId} no existe", 'status' => 404];
            }

            $lineaSubtotal = (float) $producto->precio * $cantidad;

            $lineas[] = [
                'producto_id'     => $producto->id,
                'nombre_producto' => $producto->nombre,
                'cantidad'        => $cantidad,
                'precio_unitario' => (float) $producto->precio,
                'subtotal'        => $lineaSubtotal,
            ];

            $subtotal += $lineaSubtotal;
        }

        return ['lineas' => $lineas, 'subtotal' => $subtotal];
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
