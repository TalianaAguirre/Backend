<?php

use App\Controllers\CategoriaController;
use App\Controllers\ProductoController;
use App\Middleware\AuthMiddleware;

$app->get('/api/categorias', [CategoriaController::class, 'listar'])->add(AuthMiddleware::class);

$app->get('/api/productos', [ProductoController::class, 'listar'])->add(AuthMiddleware::class);
$app->post('/api/productos', [ProductoController::class, 'crear'])->add(AuthMiddleware::class);
$app->put('/api/productos/{id}', [ProductoController::class, 'actualizar'])->add(AuthMiddleware::class);
$app->delete('/api/productos/{id}', [ProductoController::class, 'eliminar'])->add(AuthMiddleware::class);
