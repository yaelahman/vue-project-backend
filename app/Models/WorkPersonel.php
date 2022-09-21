<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkPersonel extends Model
{
    protected $connection = 'pgsql';
    public $incrementing = true;
    public $timestamps = false;
    protected $table = "m_work_personel";
    protected $primaryKey = 'id_m_work_personel';
    protected $guarded = [];

    public function getPersonel()
    {
        return $this->hasOne('App\Models\Personel', 'id_m_personel', 'id_m_personel');
    }

    public function getWorkPattern()
    {
        return $this->hasOne('App\Models\WorkPatern', 'id_m_work_patern', 'id_m_work_patern');
    }

    public function getWorkSchedule()
    {
        return $this->hasOne('App\Models\WorkSchedule', 'id_m_work_patern', 'id_m_work_patern');
    }
}
