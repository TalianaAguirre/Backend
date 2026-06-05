<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'mesa_id',
        'fecha',
        'hora',
        'subtotal',
        'total',
        'estado',
    ];

    protected $appends = ['cantidad_total'];

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    public function getCantidadTotalAttribute(): int
    {
        return (int) $this->detalles->sum('cantidad');
    }
}
