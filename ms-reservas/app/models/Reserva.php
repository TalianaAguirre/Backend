<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';

    protected $fillable = [
        'nombre_cliente',
        'telefono_cliente',
        'cantidad_personas',
        'fecha',
        'hora',
        'observaciones',
        'estado',
        'mesa_id',
    ];
}
