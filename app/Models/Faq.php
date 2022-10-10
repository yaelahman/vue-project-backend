<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{

    protected $connection = 'pgsql';
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id_m_faq';

    protected $table = "m_faq";
}
