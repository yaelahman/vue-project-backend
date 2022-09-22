<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeEmail extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'change_email';
    // protected $primaryKey = false;
    public $incrementing = true;
    public $timestamps = false;
}
