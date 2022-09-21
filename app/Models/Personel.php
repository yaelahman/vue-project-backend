<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personel extends Model
{
    protected $connection = 'pgsql';
    public $incrementing = true;
    public $timestamps = false;
    protected $table = "m_personel";
    protected $primaryKey = 'id_m_personel';
    protected $guarded = [];

    public function Departemen()
    {
        return $this->hasOne('App\Models\Departemen', 'id_m_departemen', 'id_m_departemen');
    }

    public function Absensi()
    {
        return $this->hasOne('App\Models\Absensi', 'id_m_personel', 'id_m_personel')->orderBy('id_t_absensi', 'desc');
    }
}
