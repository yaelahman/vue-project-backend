<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkPatern extends Model
{
    protected $connection = 'pgsql';
    public $incrementing = true;
    public $timestamps = false;
    protected $table = "m_work_patern";
    protected $primaryKey = 'id_m_work_patern';
    protected $guarded = [];

    public function getWorkSchedule()
    {
        return $this->hasMany('App\Models\WorkSchedule', 'id_m_work_patern', 'id_m_work_patern')->orderBy('id_m_work_schedule', 'asc');
    }

    public function WPDKerja()
    {
        return $this->hasMany('App\Models\WorkSchedule', 'id_m_work_patern', 'id_m_work_patern');
    }

    public function WPDLibur()
    {
        return $this->hasMany('App\Models\WorkSchedule', 'id_m_work_patern', 'id_m_work_patern');
    }

    public function WorkPersonel()
    {
        return $this->hasMany('App\Models\WorkPersonel', 'id_m_work_patern', 'id_m_work_patern');
    }
}
