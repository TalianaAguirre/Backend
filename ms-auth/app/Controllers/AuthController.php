<?php

namespace App\Controllers;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function login(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $login = $body['login'] ?? '';
        $contrasena = $body['contrasena'] ?? '';

        if (empty($login) || empty($contrasena)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Usuario y contraseña son requeridos',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $usuario = Usuario::where(function ($query) use ($login) {
            $query->where('usuario', $login)->orWhere('correo', $login);
        })->where('estado', 'activo')->first();

        if (!$usuario || $contrasena !== $usuario->contrasena) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Credenciales incorrectas',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = bin2hex(random_bytes(32));

        $usuario->token = $token;
        $usuario->sesion_activa = true;
        $usuario->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'token'   => $token,
            'usuario' => $usuario->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function logout(Request $request, Response $response): Response
    {
        $token = $this->extractToken($request);

        $usuario = Usuario::where('token', $token)->where('sesion_activa', true)->first();

        if (!$usuario) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Token inválido o sesión ya cerrada',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $usuario->token = null;
        $usuario->sesion_activa = false;
        $usuario->save();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function validate(Request $request, Response $response): Response
    {
        $token = $this->extractToken($request);

        $usuario = Usuario::where('token', $token)->where('sesion_activa', true)->first();

        if (!$usuario) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Token inválido o sesión expirada',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'usuario' => $usuario->toArray(),
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    private function extractToken(Request $request): string
    {
        $header = $request->getHeaderLine('Authorization');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return '';
    }
}
