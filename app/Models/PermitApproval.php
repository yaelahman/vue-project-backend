<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermitApproval extends Model
{
    protected $connection = 'pgsql';
    protected $table = 't_permit_approval';
    protected $primaryKey = 'id_permit_approval';
}
