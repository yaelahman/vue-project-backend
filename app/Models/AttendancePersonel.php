<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendancePersonel extends Model
{
    protected $connection = 'pgsql';
    public $incrementing = true;
    public $timestamps = false;
    protected $table = "m_attendance_personel";
    protected $primaryKey = 'id_m_attendance_personel';
    protected $guarded = [];

    public function getPersonel()
    {
        return $this->hasOne('App\Models\Personel', 'id_m_personel', 'id_m_personel');
    }
}
