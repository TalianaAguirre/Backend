<?php

namespace App\Config;

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

class Database
{
    public static function initialize(): void
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();

        $capsule = new Capsule();

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'],
            'port'      => $_ENV['DB_PORT'],
            'database'  => $_ENV['DB_DATABASE'],
            'username'  => $_ENV['DB_USERNAME'],
            'password'  => $_ENV['DB_PASSWORD'],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['AUTH_DB_HOST'],
            'port'      => $_ENV['AUTH_DB_PORT'],
            'database'  => $_ENV['AUTH_DB_DATABASE'],
            'username'  => $_ENV['AUTH_DB_USERNAME'],
            'password'  => $_ENV['AUTH_DB_PASSWORD'],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ], 'auth');

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['RESERVAS_DB_HOST'],
            'port'      => $_ENV['RESERVAS_DB_PORT'],
            'database'  => $_ENV['RESERVAS_DB_DATABASE'],
            'username'  => $_ENV['RESERVAS_DB_USERNAME'],
            'password'  => $_ENV['RESERVAS_DB_PASSWORD'],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ], 'reservas');

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['PRODUCTOS_DB_HOST'],
            'port'      => $_ENV['PRODUCTOS_DB_PORT'],
            'database'  => $_ENV['PRODUCTOS_DB_DATABASE'],
            'username'  => $_ENV['PRODUCTOS_DB_USERNAME'],
            'password'  => $_ENV['PRODUCTOS_DB_PASSWORD'],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ], 'productos');

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
