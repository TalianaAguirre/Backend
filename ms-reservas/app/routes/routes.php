<?php

use App\Controllers\MesaController;
use App\Controllers\ReservaController;
use App\Middleware\AuthMiddleware;

$app->get('/api/mesas', [MesaController::class, 'listar'])->add(AuthMiddleware::class);
$app->post('/api/mesas', [MesaController::class, 'crear'])->add(AuthMiddleware::class);
$app->put('/api/mesas/{id}', [MesaController::class, 'actualizar'])->add(AuthMiddleware::class);
$app->put('/api/mesas/{id}/estado', [MesaController::class, 'cambiarEstado'])->add(AuthMiddleware::class);

$app->get('/api/reservas', [ReservaController::class, 'listar'])->add(AuthMiddleware::class);
$app->post('/api/reservas', [ReservaController::class, 'crear'])->add(AuthMiddleware::class);
$app->put('/api/reservas/{id}', [ReservaController::class, 'actualizar'])->add(AuthMiddleware::class);
$app->put('/api/reservas/{id}/cancelar', [ReservaController::class, 'cancelar'])->add(AuthMiddleware::class);
