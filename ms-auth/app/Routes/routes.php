<?php

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

$app->post('/api/auth/login', [AuthController::class, 'login']);
$app->post('/api/auth/logout', [AuthController::class, 'logout'])->add(AuthMiddleware::class);
$app->get('/api/auth/validate', [AuthController::class, 'validate'])->add(AuthMiddleware::class);
