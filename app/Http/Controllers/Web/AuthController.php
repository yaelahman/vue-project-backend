<?php

namespace App\Http\Controllers\Web;

use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Mail\MailUser;
use App\Mail\ResetPassword;
use App\Models\UserCompany;
use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
        // $this->middleware('guest')->except('Logout');
        $this->middleware('auth:api', ['except' => [
            'prosesLogin', 'register', 'forgotPassword', 'resetPassword'
        ]]);
    }

    public function indexLogin()
    {
        return view('auth.login');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_perusahaan' => 'required',
            'bidang_usaha' => 'required',
            'no_telp' => 'required|min:12|max:13',
            'timezone' => 'required',
            'total_anggota' => 'required',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|min:6|same:password',
        ], [
            'required' => ':Attribute harus diisi.',
            'string'   => ':Attribute harus berupa string.',
            'unique'   => ':Attribute telah digunakan.',
            'min'      => ':Attribute minimal :min karakter.',
            'max'      => ':Attribute maximal :max karakter.',
            'same'     => ':Attribute tidak sama.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        DB::beginTransaction();
        try {
            $user_company = new UserCompany();
            $user_company->created_at = Carbon::now();

            $user_company->m_user_company_name = $request['nama_perusahaan'];
            $user_company->m_user_company_phone = $request['no_telp'];
            $user_company->m_user_company_email = $request['email'];
            $user_company->m_user_company_timeZone = $request['timezone'];
            $user_company->m_user_company_total_personel = $request['total_anggota'];
            $user_company->m_user_company_joinDate = Carbon::now();
            $user_company->id_m_company_industri = $request['bidang_usaha'];
            $user_company->updated_at = Carbon::now();
            $user_company->save();

            $hashedPassword = Hash::make($request['password']);

            //ADMIN
            $generate_user = new User();
            $generate_user->name = $user_company->m_user_company_name;
            $generate_user->email = $user_company->m_user_company_email;
            $generate_user->password = $hashedPassword;
            $generate_user->id_m_user_company = $user_company->id_m_user_company;
            $generate_user->created_at = Carbon::now();
            $generate_user->updated_at = Carbon::now();
            $generate_user->status = User::STATUS_MAP['Gratis Awal'];
            $generate_user->updated_status_at = Carbon::now();
            $generate_user->save();

            //assign role with permission to user
            $user = User::find($generate_user->id);
            $user->assignRole('ADMIN');

            $order = [
                'link' => route('verify.email.user', ['id' => $user->id]),
                'subject' => 'Verify Email Address',
            ];
            $user->notify(new MailUser($order));
            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Registrasi Berhasil, Silahkan Check Email Untuk Verifikasi Akun'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 401,
                'message' => 'Registrasi Gagal, Silahkan Hubungi Admin'
            ], 401);
            throw $e;
        }
    }

    public function prosesLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $credentials = $request->only('email', 'password');
        $user = User::isCheckUser($request->email);
        // return $user;
        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Login Gagal, User Tidak Ditemukan'
            ], 401);
        } else if ($user->email_verified_at == null) {
            return response()->json([
                'status' => 401,
                'message' => 'Login Gagal, Email Belum Verifikasi'
            ], 401);
        } else if ($user->status == (null || 0) && $user->id_m_user_company != null) {
            return response()->json([
                'status' => 401,
                'message' => 'Login Gagal', 'swal' => true
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 401,
                'message' => 'Login Gagal, Periksa Username atau Password Anda'
            ], 401);
        }

        // return "OKE";

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Gagal, User tidak ditemukan'
            ]);
        }

        $reset = PasswordReset::where('email', $user->email)->first();
        if (!$reset) {
            $reset = new PasswordReset();
        }
        $reset->email = $user->email;
        $reset->token = Str::random(40);
        $reset->created_at = Carbon::now();
        $reset->save();


        $order = [
            'link' => env('APP_URL_FRONTEND') . "/password-reset/$reset->token?email=$reset->email",
            'subject' => 'Reset Password',
        ];
        $user->notify(new ResetPassword($order));

        return response()->json([
            'status' => 200,
            'message' => 'Lupa Password Berhasil, Silahkan Check Email Anda'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $reset = PasswordReset::where([
            'email' => $request->email,
            'token' => $request->token
        ])->first();

        if (!$reset) {
            return response()->json([
                'status' => 401,
                'message' => 'Permintaan Lupa Password Telah Berakhir, Silahkan Lakukan Kembali'
            ]);
        }

        $now = Carbon::now();
        $date = new Carbon($reset->created_at);

        if ($date->diffInHours($now, false) >= 1) {
            return response()->json([
                'status' => 401,
                'message' => 'Permintaan Lupa Password Telah Berakhir, Silahkan Lakukan Kembali'
            ]);
        }
        // return $date;
        // return $date->diffInHours($now, false);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|min:6|same:password',
        ], [
            'required' => ':Attribute harus diisi.',
            'string'   => ':Attribute harus berupa string.',
            'same'     => ':Attribute tidak sama.'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            $reset->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil Mengubah Password, Silahkan Login Untuk Mengakses'
            ]);
        }
        return response()->json([
            'status' => 401,
            'message' => 'Terjadi Kesalahan'
        ]);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 200,
            'message' => 'Berhasil Login, sedang mengarahkan ke halaman utama',
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ]);
    }

    public function Logout()
    {
        return response()->json(Auth::logout());
    }
}
