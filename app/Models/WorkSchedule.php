<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $connection = 'pgsql';
    public $incrementing = true;
    public $timestamps = false;
    protected $table = "m_work_schedule";
    protected $primaryKey = 'id_m_work_schedule';
    protected $guarded = [];

    public function getWorkPattern()
    {
        return $this->belongsTo('App\Models\WorkPatern', 'id_m_work_patern', 'id_m_work_patern');
    }
}
