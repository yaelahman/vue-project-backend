<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermitDate extends Model
{

    protected $connection = 'pgsql';
    protected $table = 't_permit_date';
    protected $primaryKey = 'id_permit_date';

    public function Permit()
    {
        return $this->hasOne('App\Models\Permit', 'id_permit_application', 'id_permit_application');
    }
}
