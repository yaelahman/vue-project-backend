<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    protected $connection = 'pgsql';
    public $incrementing = true;
    public $timestamps = false;
    protected $table = "m_departemen";
    protected $primaryKey = 'id_m_departemen';
    protected $guarded = [];

    public function Personels()
    {
        return $this->hasMany('App\Models\Personel', 'id_m_departemen', 'id_m_departemen');
    }
}
