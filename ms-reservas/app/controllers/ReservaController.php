<?php

namespace App\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReservaController
{
    private array $estados = ['pendiente', 'confirmada', 'cancelada', 'finalizada'];

    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query = Reserva::query();

        if (!empty($params['fecha'])) {
            $query->where('fecha', $params['fecha']);
        }

        if (!empty($params['cliente'])) {
            $query->where('nombre_cliente', 'like', '%' . $params['cliente'] . '%');
        }

        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        $reservas = $query->orderBy('fecha')->orderBy('hora')->get();

        $response->getBody()->write(json_encode([
            'success'  => true,
            'reservas' => $reservas->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function crear(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        $nombre = trim($body['nombre_cliente'] ?? '');
        $telefono = trim($body['telefono_cliente'] ?? '');
        $personas = (int) ($body['cantidad_personas'] ?? 0);
        $fecha = $body['fecha'] ?? '';
        $hora = $body['hora'] ?? '';
        $mesaId = (int) ($body['mesa_id'] ?? 0);
        $observaciones = $body['observaciones'] ?? null;
        $estado = $body['estado'] ?? 'pendiente';

        if ($nombre === '' || $telefono === '' || $fecha === '' || $hora === '' || $mesaId <= 0) {
            return $this->error($response, 'Faltan datos obligatorios de la reserva', 400);
        }

        if ($personas <= 0) {
            return $this->error($response, 'La cantidad de personas debe ser mayor a cero', 400);
        }

        if (!in_array($estado, $this->estados, true)) {
            return $this->error($response, 'Estado de reserva inválido', 400);
        }

        if ($fecha < date('Y-m-d')) {
            return $this->error($response, 'No se permiten reservas en fechas pasadas', 400);
        }

        $mesa = Mesa::find($mesaId);
        if (!$mesa) {
            return $this->error($response, 'La mesa no existe', 404);
        }

        if ($mesa->estado === 'fuera_servicio') {
            return $this->error($response, 'La mesa está fuera de servicio', 409);
        }

        if ($personas > $mesa->capacidad) {
            return $this->error($response, 'La cantidad de personas supera la capacidad de la mesa', 409);
        }

        if ($this->mesaOcupada($mesaId, $fecha, $hora)) {
            return $this->error($response, 'La mesa ya tiene una reserva para esa fecha y hora', 409);
        }

        $reserva = Reserva::create([
            'nombre_cliente'    => $nombre,
            'telefono_cliente'  => $telefono,
            'cantidad_personas' => $personas,
            'fecha'             => $fecha,
            'hora'              => $hora,
            'observaciones'     => $observaciones,
            'estado'            => $estado,
            'mesa_id'           => $mesaId,
        ]);

        $response->getBody()->write(json_encode([
            'success' => true,
            'reserva' => $reserva->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function actualizar(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::find($args['id']);

        if (!$reserva) {
            return $this->error($response, 'Reserva no encontrada', 404);
        }

        $body = $request->getParsedBody();

        $fecha = $body['fecha'] ?? $reserva->fecha;
        $hora = $body['hora'] ?? $reserva->hora;
        $mesaId = (int) ($body['mesa_id'] ?? $reserva->mesa_id);
        $personas = (int) ($body['cantidad_personas'] ?? $reserva->cantidad_personas);

        if ($personas <= 0) {
            return $this->error($response, 'La cantidad de personas debe ser mayor a cero', 400);
        }

        if ($fecha < date('Y-m-d')) {
            return $this->error($response, 'No se permiten reservas en fechas pasadas', 400);
        }

        $mesa = Mesa::find($mesaId);
        if (!$mesa) {
            return $this->error($response, 'La mesa no existe', 404);
        }

        if ($mesa->estado === 'fuera_servicio') {
            return $this->error($response, 'La mesa está fuera de servicio', 409);
        }

        if ($personas > $mesa->capacidad) {
            return $this->error($response, 'La cantidad de personas supera la capacidad de la mesa', 409);
        }

        if ($this->mesaOcupada($mesaId, $fecha, $hora, $reserva->id)) {
            return $this->error($response, 'La mesa ya tiene una reserva para esa fecha y hora', 409);
        }

        $reserva->fecha = $fecha;
        $reserva->hora = $hora;
        $reserva->mesa_id = $mesaId;
        $reserva->cantidad_personas = $personas;

        if (array_key_exists('observaciones', $body)) {
            $reserva->observaciones = $body['observaciones'];
        }

        if (isset($body['estado'])) {
            if (!in_array($body['estado'], $this->estados, true)) {
                return $this->error($response, 'Estado de reserva inválido', 400);
            }
            $reserva->estado = $body['estado'];
        }

        $reserva->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'reserva' => $reserva->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function cancelar(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::find($args['id']);

        if (!$reserva) {
            return $this->error($response, 'Reserva no encontrada', 404);
        }

        $reserva->estado = 'cancelada';
        $reserva->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Reserva cancelada correctamente',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    private function mesaOcupada(int $mesaId, string $fecha, string $hora, ?int $excluir = null): bool
    {
        $query = Reserva::where('mesa_id', $mesaId)
            ->where('fecha', $fecha)
            ->where('hora', $hora)
            ->where('estado', '!=', 'cancelada');

        if ($excluir !== null) {
            $query->where('id', '!=', $excluir);
        }

        return $query->exists();
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
