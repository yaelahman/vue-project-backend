<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $connection = 'pgsql';
    protected $table = 't_absensi';
    protected $primaryKey = 'id_t_absensi';

    const STATUS_CLOCKIN    = 1;
    const STATUS_CLOCKOUT   = 2;

    const TEXT_CLOCKIN      = 'clockIn';
    const TEXT_CLOCKOUT     = 'clockOut';

    public function Personel()
    {
        return $this->hasOne('App\Models\Personel', 'id_m_personel', 'id_m_personel');
    }

    public function WorkPersonel()
    {
        return $this->hasOne('App\Models\WorkPersonel', 'id_m_personel', 'id_m_personel');
    }


    public function PhotoAbsensi()
    {
        return $this->hasMany('App\Models\AbsensiPhoto', 'id_t_absensi', 'id_t_absensi');
    }
}
