<?php

namespace App\Http\Controllers\Web;

use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Personel;
use App\Models\UserCompany;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DepartemenController extends Controller
{
    public function index(Request $request)
    {
        $auth = Auth::user();
        $departemen = Departemen::where('id_m_user_company', $auth->id_m_user_company)->withCount(['Personels' => function ($query) use ($auth) {
            $query->where('id_m_user_company', $auth->id_m_user_company);
            $query->where('m_personel_status', 1);
        }]);

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $departemen->get()
        );
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $message = 'Berhasil menambahkan Departemen';

            if ($request->id != null) {

                $message = 'Berhasil memperbarui Departemen';
                $departemen = Departemen::findOrFail($request->id);
            } else {
                $departemen = new Departemen();
                $departemen->created_at = Carbon::now();
            }
            $departemen->id_m_user_company = Auth::user()->id_m_user_company;
            $departemen->m_departemen_name = $request['departemen']['m_departemen_name'];
            $departemen->updated_at = Carbon::now();
            $departemen->save();

            DB::commit();

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message
                    . ' Departemen ' . $departemen->m_departemen_name
            );
        } catch (\Exception $e) {
            DB::rollback();
            // throw $e;
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                Fungsi::MES_CREATE_EDIT
            );
        }
    }

    public function detail($id)
    {
        $auth = Auth::user();
        $departemen = Departemen::where('id_m_user_company', $auth->id_m_user_company)->firstOrFail($id);

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $departemen
        );
    }

    public function delete($id)
    {
        $message = 'Berhasil menghapus Departemen';

        $departemen = Departemen::findOrFail($id);
        $check = Personel::where('id_m_departemen', $departemen->id_m_departemen)->count();
        if ($check != null) {
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                "Gagal mengahpus "
                    . " Departemen " . $departemen->m_departemen_name
                    . " karena masih digunakan"
            );
        }
        $departemen->delete();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
                . ' Departemen ' . $departemen->m_departemen_name
        );
    }
}
