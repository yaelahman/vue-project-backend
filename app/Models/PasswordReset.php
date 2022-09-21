<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{

    protected $connection = 'pgsql';
    protected $table = 'password_resets';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
}
