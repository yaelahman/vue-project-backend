<?php

namespace App\Http\Controllers\Web;

use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\WorkPersonel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonelTimeWorkController extends Controller
{
    public function index(Request $request)
    {
        $auth = Auth::user();
        $personel_time_works = WorkPersonel::with(['getPersonel' => function ($query) {
            $query->with('Departemen');
        }])->with('getWorkPattern')
            ->where('id_m_user_company', $auth->id_m_user_company)
            ->orderBy('id_m_work_personel', 'DESC');

        if (isset($request->id_work_pattern) && $request->id_work_pattern != null) {
            $personel_time_works->where('id_m_work_patern', $request->id_work_pattern);
        }

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $personel_time_works->get()
        );
    }

    public function getDataPersonel()
    {
        $auth = Auth::user();
        $get_personel_time_works = WorkPersonel::select('id_m_personel', 'id_m_user_company')->where('id_m_user_company', $auth->id_m_user_company)->get();

        $data = [];

        foreach ($get_personel_time_works as $get_personel_time_work) {
            $data['id_m_personel'] = array_push($data, $get_personel_time_work->id_m_personel);
        }

        $get_personel = Personel::where('id_m_user_company', $auth->id_m_user_company)->whereNotIn('id_m_personel', $data)->get();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $get_personel
        );
    }

    public function getDataEditPersonel(Request $request)
    {
        $auth = Auth::user();
        $get_personel = Personel::where('id_m_user_company', $auth->id_m_user_company)->whereNotIn('id_m_personel', WorkPersonel::select('id_m_personel')->where('id_m_work_personel', '!=', $request->except)->get())->get();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $get_personel
        );
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {

            $check = WorkPersonel::where('id_m_personel', $request['personel_time_work']['id_m_personel'])->first();
            if ($check) {
                return $this->sendResponse(
                    Fungsi::STATUS_ERROR,
                    "Gagal, karena personel telah terdaftar Jadwal Kerja"
                );
            }
            $auth = Auth::user();
            $message = 'Berhasil menambahkan Personel Time Work';

            if ($request->id != null) {
                $message = 'Berhasil memperbarui menambahkan Personel Time Work';
                $personel_time_work = WorkPersonel::find($request->id);
                if (!$personel_time_work) {

                    $personel_time_work = new WorkPersonel();
                    $personel_time_work->created_at = Carbon::now();
                }
            } else {
                $personel_time_work = new WorkPersonel();
                $personel_time_work->created_at = Carbon::now();
            }
            $personel_time_work->id_m_user_company = $auth->id_m_user_company;
            $personel_time_work->id_m_personel = $request['personel_time_work']['id_m_personel'];
            $personel_time_work->id_m_work_patern = $request['personel_time_work']['id_m_work_patern'];
            $personel_time_work->m_work_personel_time = $request['personel_time_work']['m_work_personel_time'];
            $personel_time_work->updated_at = Carbon::now();
            $personel_time_work->save();

            DB::commit();

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message
            );
        } catch (\Throwable $th) {
            DB::rollback();
            // Log::info($th);
            // throw $th;
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                Fungsi::MES_CREATE_EDIT
            );
        }
    }

    public function detail($id)
    {
        $personel_time_work = WorkPersonel::findOrFail($id);

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $personel_time_work
        );
    }

    public function delete($id)
    {
        $message = 'Berhasil menghapus Personel Time Work';

        $personel_time_work = WorkPersonel::findOrFail($id);
        $personel_time_work->delete();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
        );
    }
}
