<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MailUser;
use App\Providers\RouteServiceProvider;
use App\User;
use App\Models\UserCompany;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nama_perusahaan' => 'required',
            'bidang_usaha' => 'required',
            'timezone' => 'required',
            'no_telp' => 'required|min:12|max:13',
            'total_anggota' => 'required',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|min:6|same:password',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        DB::beginTransaction();
        try {
            $user_company = new UserCompany();
            $user_company->created_at = Carbon::now();

            $user_company->m_user_company_name = $data['nama_perusahaan'];
            $user_company->m_user_company_phone = $data['no_telp'];
            $user_company->m_user_company_email = $data['email'];
            $user_company->m_user_company_total_personel = $data['total_anggota'];
            $user_company->m_user_company_timeZone = $data['timezone'];
            $user_company->m_user_company_joinDate = Carbon::now();
            $user_company->id_m_company_industri = $data['bidang_usaha'];
            $user_company->updated_at = Carbon::now();
            $user_company->save();

            $hashedPassword = Hash::make($data['password']);

            //ADMIN
            $generate_user = new User();
            $generate_user->name = $user_company->m_user_company_name;
            $generate_user->email = $user_company->m_user_company_email;
            $generate_user->password = $hashedPassword;
            $generate_user->id_m_user_company = $user_company->id_m_user_company;
            $generate_user->created_at = Carbon::now();
            $generate_user->updated_at = Carbon::now();
            $generate_user->save();

            //assign role with permission to user
            $user = User::find($generate_user->id);
            $user->assignRole('ADMIN');

            return $user;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
