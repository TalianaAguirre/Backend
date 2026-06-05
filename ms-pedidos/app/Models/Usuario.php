<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $connection = 'auth';

    protected $table = 'usuarios';

    protected $hidden = ['contrasena'];
}
