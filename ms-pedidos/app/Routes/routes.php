<?php

use App\Controllers\PedidoController;
use App\Middleware\AuthMiddleware;

global $app;
$app->get('/api/pedidos', [PedidoController::class, 'listar'])->add(AuthMiddleware::class);
$app->get('/api/pedidos/{id}', [PedidoController::class, 'ver'])->add(AuthMiddleware::class);
$app->post('/api/pedidos', [PedidoController::class, 'crear'])->add(AuthMiddleware::class);
$app->put('/api/pedidos/{id}', [PedidoController::class, 'actualizar'])->add(AuthMiddleware::class);
$app->put('/api/pedidos/{id}/estado', [PedidoController::class, 'cambiarEstado'])->add(AuthMiddleware::class);
