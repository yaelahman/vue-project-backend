<?php

namespace App;

use App\Models\Personel;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notification;


class User extends Model implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasRoles, \Illuminate\Notifications\Notifiable;
    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function isCheckUser($email)
    {
        $user = User::where('email', $email)->with('Personel')->first();

        return $user;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function Personel()
    {
        return $this->hasOne(Personel::class, 'id_m_user_company', 'id_m_user_company');
    }

    const STATUS = [
        0 => 'Tidak Aktif',
        1 => 'Gratis Awal',
        2 => 'Gratis',
        3 => 'Berbayar',
    ];

    const STATUS_MAP = [
        'Tidak Aktif' => 0,
        'Gratis Awal' => 1,
        'Gratis' => 2,
        'Berbayar' => 3
    ];
}
