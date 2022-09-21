<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCompany extends Model
{
    protected $connection = 'pgsql';
    public $incrementing = true;
    public $timestamps = false;
    protected $table = "m_user_company";
    protected $primaryKey = 'id_m_user_company';
    protected $guarded = [];

    public function companyIndustri()
    {
        return $this->hasOne('App\Models\CompanyIndustri', 'id_m_company_industri', 'id_m_company_industri');
    }
}
