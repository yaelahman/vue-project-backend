<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permit extends Model
{

    protected $connection = 'pgsql';
    protected $table = 't_permit_application';
    protected $primaryKey = 'id_permit_application';

    const TYPE = [
        1 => 'Jam',
        2 => 'Hari',
        3 => 'Cuti',
    ];

    const STATUS = [
        0 => 'Menunggu Persetujuan',
        1 => 'Diterima',
        2 => 'Ditolak',
        3 => 'Kadaluarsa'
    ];

    public function Personel()
    {
        return $this->hasOne('App\Models\Personel', 'id_m_personel', 'id_m_personel');
    }

    public function PermitApproval()
    {
        return $this->hasOne('App\Models\PermitApproval', 'id_permit_application', 'id_permit_application');
    }

    public function PermitDate()
    {
        return $this->hasMany('App\Models\PermitDate', 'id_permit_application', 'id_permit_application');
    }

    public function PermitImage()
    {
        return $this->hasMany('App\Models\PermitImage', 'id_permit_application', 'id_permit_application');
    }

    public static function ExpiredPermit()
    {
        $permit = Permit::where('created_at', '>', date('Y-m-d'))->get();
    }
}
