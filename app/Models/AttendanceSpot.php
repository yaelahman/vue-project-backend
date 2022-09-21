<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSpot extends Model
{
    protected $connection = 'pgsql';
    public $incrementing = true;
    public $timestamps = false;
    protected $table = "m_attendance_spots";
    protected $primaryKey = 'id_m_attendance_spots';
    protected $guarded = [];

    public function getUserCompany()
    {
        return $this->hasOne('App\Models\UserCompany', 'id_m_user_company', 'id_m_user_company');
    }
}
