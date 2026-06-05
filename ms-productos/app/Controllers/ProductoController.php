<?php

namespace App\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductoController
{
    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query = Producto::with('categoria');

        if (!empty($params['categoria_id'])) {
            $query->where('categoria_id', (int) $params['categoria_id']);
        }

        if (isset($params['disponible']) && $params['disponible'] !== '') {
            $query->where('disponible', filter_var($params['disponible'], FILTER_VALIDATE_BOOLEAN));
        }

        $productos = $query->orderBy('nombre')->get();

        $response->getBody()->write(json_encode([
            'success'   => true,
            'productos' => $productos->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function crear(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $nombre = trim($body['nombre'] ?? '');
        $precio = $body['precio'] ?? null;
        $categoriaId = (int) ($body['categoria_id'] ?? 0);
        $descripcion = $body['descripcion'] ?? null;
        $disponible = isset($body['disponible']) ? (bool) $body['disponible'] : true;

        if ($nombre === '') {
            return $this->error($response, 'El nombre del producto es requerido', 400);
        }

        if (!is_numeric($precio) || (float) $precio <= 0) {
            return $this->error($response, 'El precio debe ser mayor a cero', 400);
        }

        if ($categoriaId <= 0 || !Categoria::find($categoriaId)) {
            return $this->error($response, 'La categoría no existe', 404);
        }

        if (Producto::where('nombre', $nombre)->exists()) {
            return $this->error($response, 'Ya existe un producto con ese nombre', 409);
        }

        $producto = Producto::create([
            'nombre'       => $nombre,
            'descripcion'  => $descripcion,
            'precio'       => (float) $precio,
            'disponible'   => $disponible,
            'categoria_id' => $categoriaId,
        ]);

        $producto->load('categoria');

        $response->getBody()->write(json_encode([
            'success'  => true,
            'producto' => $producto->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function actualizar(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {
            return $this->error($response, 'Producto no encontrado', 404);
        }

        $body = $request->getParsedBody();

        if (isset($body['nombre'])) {
            $nombre = trim($body['nombre']);
            if ($nombre === '') {
                return $this->error($response, 'El nombre del producto es requerido', 400);
            }
            if (Producto::where('nombre', $nombre)->where('id', '!=', $producto->id)->exists()) {
                return $this->error($response, 'Ya existe un producto con ese nombre', 409);
            }
            $producto->nombre = $nombre;
        }

        if (isset($body['precio'])) {
            if (!is_numeric($body['precio']) || (float) $body['precio'] <= 0) {
                return $this->error($response, 'El precio debe ser mayor a cero', 400);
            }
            $producto->precio = (float) $body['precio'];
        }

        if (isset($body['categoria_id'])) {
            $categoriaId = (int) $body['categoria_id'];
            if ($categoriaId <= 0 || !Categoria::find($categoriaId)) {
                return $this->error($response, 'La categoría no existe', 404);
            }
            $producto->categoria_id = $categoriaId;
        }

        if (array_key_exists('descripcion', $body)) {
            $producto->descripcion = $body['descripcion'];
        }

        if (isset($body['disponible'])) {
            $producto->disponible = (bool) $body['disponible'];
        }

        $producto->save();
        $producto->load('categoria');

        $response->getBody()->write(json_encode([
            'success'  => true,
            'producto' => $producto->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function eliminar(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {
            return $this->error($response, 'Producto no encontrado', 404);
        }

        $producto->delete();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Producto eliminado correctamente',
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
