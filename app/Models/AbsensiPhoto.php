<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiPhoto extends Model
{
    protected $connection = 'pgsql';
    protected $table = 't_absensi_photo';
    protected $primaryKey = 'id_t_absensi_photo';
}
