<?php

namespace App\Http\Controllers\Web;

use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\UserCompany;
use App\Models\CompanyIndustri;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Mail\MailUser;
use Illuminate\Support\Facades\Mail;

class UserCompanyController extends Controller
{
    public function index()
    {
        $user_companies = UserCompany::with('companyIndustri')->orderBy('id_m_user_company', 'DESC')->get();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $user_companies
        );
    }

    public function create(Request $request)
    {

        DB::beginTransaction();
        try {
            $check = strlen($request['user_company']['m_user_company_phone']);
            if ($check <= 11 || $check >= 14) {
                return $this->sendResponse(
                    Fungsi::STATUS_ERROR,
                    "Gagal memasukan nomer telp, min 12 digit dan max 13 digit"
                );
            }

            $userEmail = User::where('email',  $request['user_company']['m_user_company_email']);
            $message = 'Berhasil menambahkan User Company';

            if ($request->id != null) {
                $message = 'Berhasil memperbarui User Company';
                $user_company = UserCompany::findOrFail($request->id);

                $count_personel = Personel::where('id_m_user_company', $request->id)->count();
                $total_personel = $request['user_company']['m_user_company_total_personel'];
                if ($total_personel < $count_personel) {
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Gagal. jumlah personel yang di masukan kurang dengan data personel yang sudah ada"
                    );
                }
                $userEmail->where('id_m_user_company', '!=', $request->id);
            } else {
                $user_company = new UserCompany();
                $user_company->created_at = $request['user_company']['m_user_company_joinDate'];
            }

            if ($userEmail->count() > 0) {
                return $this->sendResponse(
                    Fungsi::STATUS_ERROR,
                    "Gagal. Email telah digunakan"
                );
            }

            $user_company->m_user_company_name = $request['user_company']['m_user_company_name'];
            $user_company->m_user_company_phone = $request['user_company']['m_user_company_phone'];
            $user_company->m_user_company_email = $request['user_company']['m_user_company_email'];
            $user_company->m_user_company_total_personel = $request['user_company']['m_user_company_total_personel'];
            $user_company->m_user_company_timeZone = $request['user_company']['m_user_company_timeZone'];
            $user_company->m_user_company_joinDate = $request['user_company']['m_user_company_joinDate'];
            $user_company->id_m_company_industri = $request['user_company']['id_m_company_industri'];
            $user_company->created_at = $request['user_company']['m_user_company_joinDate'];
            $user_company->updated_at = Carbon::now();
            $user_company->save();

            $password = 'mamorasoft457';
            $hashedPassword = Hash::make($password);

            if ($request->id == null) {
                //ADMIN
                $generate_user = new User();
                $generate_user->name = $user_company->m_user_company_name;
                $generate_user->email = $user_company->m_user_company_email;
                $generate_user->password = $hashedPassword;
                $generate_user->id_m_user_company = $user_company->id_m_user_company;
                $generate_user->email_verified_at = Carbon::now();
                $generate_user->created_at = Carbon::now();
                $generate_user->updated_at = Carbon::now();
                $generate_user->save();

                //assign role with permission to user
                $user = User::find($generate_user->id);
                $user->assignRole('ADMIN');
            } else {
                $user = User::where('id_m_user_company', $request->id)->first();
                $user->name = $user_company->m_user_company_name;
                $user->email = $user_company->m_user_company_email;
                $user->updated_at = Carbon::now();
                $user->save();
            }

            DB::commit();

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message
            );
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                Fungsi::MES_CREATE_EDIT
            );
        }
    }

    public function detail($id)
    {
        $user_company = UserCompany::findOrFail($id);

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $user_company
        );
    }

    public function delete($id)
    {
        $message = 'Berhasil menghapus User Company';

        $user_company = UserCompany::findOrFail($id);
        $user_company->delete();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
        );
    }

    public function viewPage()
    {
        $company_industris = CompanyIndustri::get();

        $data = [
            'company_industry' => $company_industris
        ];

        return view('auth.register_user_company', $data);
    }

    public function saveCompany(Request $request)
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
            return redirect()->back()->withInput()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $user_company = new UserCompany();
            $user_company->created_at = Carbon::now();

            $user_company->m_user_company_name = $request->nama_perusahaan;
            $user_company->m_user_company_phone = $request->no_telp;
            $user_company->m_user_company_email = $request->email;
            $user_company->m_user_company_timeZone = $request->timezone;
            $user_company->m_user_company_total_personel = $request->total_anggota;
            $user_company->m_user_company_joinDate = Carbon::now();
            $user_company->id_m_company_industri = $request->bidang_usaha;
            $user_company->updated_at = Carbon::now();
            $user_company->save();

            $hashedPassword = Hash::make($request->password);

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

            $order = [
                'link' => route('verify.email.user', ['id' => $user->id]),
                'subject' => 'Verify Email Address',
            ];
            $user->notify(new MailUser($order));

            DB::commit();

            $request->session()->flash('message', 'Registrasi Berhasil, Silahkan Check Email Untuk Verifikasi Akun');
            $request->session()->flash('alert', 'success');
            return redirect()->to(route('login'));
        } catch (\Exception $e) {
            throw $e;
            DB::rollback();
            $request->session()->flash('message', 'Terjadi Kesalahan Saat Mendaftar.');
            $request->session()->flash('alert', 'danger');
            return redirect()->back()->withInput();
        }
    }

    public function mail(Request $request, $id)
    {
        $user = User::find($id);
        if ($user) {

            $user->email_verified_at = Carbon::now();
            $user->save();

            return redirect()->to('http://localhost:5173/login?verified_mail=true');
        }

        return redirect()->to('http://localhost:5173/login?verified_mail=false');
    }
}
