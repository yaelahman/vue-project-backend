<?php

namespace App\Http\Controllers\Web;

use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\CompanyIndustri;
use App\Models\UserCompany;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CompanyIndustriController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index']]);
    }
    public function index()
    {
        $company_industris = CompanyIndustri::get();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $company_industris
        );
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $message = 'Berhasil menambahkan Company Industri';

            if ($request->id != null) {
                $check = CompanyIndustri::where('m_company_industriCode', $request['company_industri']['m_company_industriCode'])
                    ->where('id_m_company_industri', '!=', $request->id)->count();

                if ($check != 0) {
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Gagal, Kode " . $request['company_industri']['m_company_industriCode'] . " sudah ada"
                    );
                }

                $message = 'Berhasil memperbarui Company Industri';
                $company_industri = CompanyIndustri::findOrFail($request->id);
            } else {
                $check = CompanyIndustri::where('m_company_industriCode', $request['company_industri']['m_company_industriCode'])->count();
                if ($check != 0) {
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Gagal, Kode " . $request['company_industri']['m_company_industriCode'] . " sudah ada"
                    );
                }
                $company_industri = new CompanyIndustri();
                $company_industri->created_at = Carbon::now();
            }

            $company_industri->m_company_industriCode = $request['company_industri']['m_company_industriCode'];
            $company_industri->m_company_industriFields = $request['company_industri']['m_company_industriFields'];
            $company_industri->updated_at = Carbon::now();
            $company_industri->save();

            DB::commit();

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message
                    . ' Kode ' . $company_industri->m_company_industriCode
                    . ' Kategori ' . $company_industri->m_company_industriFields
            );
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                Fungsi::MES_CREATE_EDIT
            );
        }
    }

    public function checkCode($poscode, $except_id = null)
    {
        $posisi_anper = PosisiAnper::where('poscode', $poscode);
        if (isset($except_id)) {
            $posisi_anper->where('poscode', '<>', $except_id);
        }
        $posisi_anper = $posisi_anper->count();
        return $posisi_anper > 0 ? true : false;
    }

    public function detail($id)
    {
        $company_industri = CompanyIndustri::findOrFail($id);

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $company_industri
        );
    }

    public function delete($id)
    {
        $message = 'Berhasil menghapus Company Industri';

        $company_industri = CompanyIndustri::findOrFail($id);
        $check = UserCompany::where('id_m_company_industri', $company_industri->id_m_company_industri)->count();
        if ($check != null) {
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                "Gagal mengahpus Kode " . $company_industri->m_company_industriCode
                    . " Kategori " . $company_industri->m_company_industriFields
                    . " karena masih digunakan"
            );
        }
        $company_industri->delete();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
                . ' Kode ' . $company_industri->m_company_industriCode
                . ' Kategori ' . $company_industri->m_company_industriFields
        );
    }
}
