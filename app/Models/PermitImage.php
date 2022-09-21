<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermitImage extends Model
{
    protected $connection = 'pgsql';
    protected $table = 't_permit_photo';
    protected $primaryKey = 'id_permit_photo';
}
