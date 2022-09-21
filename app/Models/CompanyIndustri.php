<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyIndustri extends Model
{
    protected $connection = 'pgsql';
    public $incrementing = true;
    public $timestamps = false;
    protected $table = "m_company_industri";
    protected $primaryKey = 'id_m_company_industri';
    protected $guarded = [];
}
