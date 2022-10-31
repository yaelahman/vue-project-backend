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

    public function WorkPersonel()
    {
        return $this->hasOne('App\Models\WorkPersonel', 'id_m_personel', 'id_m_personel');
    }

    public function Absensi()
    {
        return $this->hasOne('App\Models\Absensi', 'id_m_personel', 'id_m_personel')->orderBy('id_t_absensi', 'desc');
    }
    public function Permit()
    {
        return $this->hasOne('App\Models\Permit', 'id_m_personel', 'id_m_personel')->orderBy('id_permit_application', 'desc');
    }
}
