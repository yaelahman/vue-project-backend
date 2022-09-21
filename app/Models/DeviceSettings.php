<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceSettings extends Model
{
    protected $connection = 'pgsql';
    public $incrementing = true;
    public $timestamps = true;
    protected $table = "m_device_settings";
    protected $primaryKey = 'id_m_device_settings';
    protected $guarded = [];
}
