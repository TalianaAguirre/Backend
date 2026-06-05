<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $connection = 'reservas';

    protected $table = 'mesas';
}
