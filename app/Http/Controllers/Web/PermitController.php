<?php

namespace App\Http\Controllers\Web;

use App\Exports\PermitExport;
use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\Permit;
use App\Models\PermitApproval;
use App\Models\Personel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class PermitController extends Controller
{
    public function get(Request $request)
    {
        $auth = Auth::user();
        $type = $request->type;

        $permits = Permit::where([
            'permit_type' => $type
        ])->with([
            'Personel' => function ($q) {
                $q->with('Departemen');
            }, 'PermitDate'
        ])->whereHas('Personel', function ($q) use ($auth) {
            $q->where('id_m_user_company', $auth->id_m_user_company);
        })->orderBy('id_permit_application', 'desc');

        if ($request->startDate != null && $request->endDate != null) {
            $permits->whereBetween('created_at', [$request->startDate, $request->endDate]);
        }

        if ($request->status != null) {
            $permits->where('permit_status', $request->status);
        }

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $permits->get()
        );
    }


    public function ExportExcel(Request $request)
    {
        $auth = Auth::user();
        $type = $request->type;
        $title = Permit::TYPE[$type];

        $permits = Permit::where([
            'permit_type' => $type
        ])->with([
            'Personel' => function ($q) {
                $q->with('Departemen');
            }, 'PermitDate'
        ])->whereHas('Personel', function ($q) use ($auth) {
            $q->where('id_m_user_company', $auth->id_m_user_company);
        })->orderBy('id_permit_application', 'desc');

        if (isset($request->start_date) && isset($request->end_date)) {
            $permits->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if (isset($request->status) && $request->status != null) {
            $permits->where('permit_status', $request->status);
        }

        $start = date('d-m-Y', strtotime($request->start_date));
        $end = date('d-m-Y', strtotime($request->end_date));

        if ($permits->count() < 1) {
            return response()->json([
                'status' => 404,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        $data = [
            'permits' => $permits->get()
        ];

        $url = "Laporan Izin $title $auth->name ($start ~ $end).xlsx";
        $excel = Excel::store(new PermitExport($data, $type), $url, 'excel', null, [
            'visibility' => 'public',
        ]);

        return response()->json([
            'url' => url('excel/' . $url),
            'message' => 'Data Ditemukan',
            'status' => 200
        ]);
    }

    public function detail(Request $request, $id)
    {
        $auth = Auth::user();
        // $type = $request->type;

        $permits = Permit::where([
            'id_permit_application' => $id
        ])->with([
            'Personel' => function ($q) {
                $q->with('Departemen');
            },
            'PermitDate', 'PermitImage', 'PermitApproval'
        ])->whereHas('Personel', function ($q) use ($auth) {
            $q->where('id_m_user_company', $auth->id_m_user_company);
        });

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $permits->first()
        );
    }

    public function approve(Request $request)
    {
        if (!isset($request['catatan'])) {
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                'Harap inputkan catatan'
            );
        }
        DB::beginTransaction();
        try {
            $auth = Auth::user();
            $id = $request['id'];
            $type = $request['type'];
            $message = "Berhasil $type izin";

            $permit = Permit::findOrFail($id);
            $permit->permit_status = $type == 'tolak' ? 2 : 1;
            $permit->save();

            $personel = Personel::find($permit->id_m_personel);
            if ($personel->remaining_leave < 1 && $permit->permit_type == 3 && $type != 'tolak') {
                return $this->sendResponse(
                    Fungsi::STATUS_ERROR,
                    "Jatah Cuti $personel->m_personel_names Telah Habis"
                );
            }
            $personel->remaining_leave = $type == 'setuju' ? ($personel->remaining_leave - 1) : $personel->remaining_leave;

            $approval = PermitApproval::where('id_permit_application', $id)->first();
            if (!$approval) $approval = new PermitApproval();
            $approval->id_permit_application =  $id;
            $approval->id_m_user_company = $auth->id_m_user_company;
            $approval->permit_approval_status = $type == 'tolak' ? 2 : 1;
            $approval->permit_approval_reason = $request['catatan'];
            $approval->save();

            DB::commit();

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message
            );
        } catch (\Exception $th) {
            // Log::info($th);
            throw $th;
            DB::rollback();
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                "Terjadi Kesalahan",
            );
        }
    }

    public function destroy($type, $id = 0)
    {
        // return $id;
        $message = 'Berhasil menghapus Data Izin';

        $permit = Permit::where([
            'id_permit_application' => $id,
            'permit_type' => $type
        ])->firstOrFail();
        $permit->delete();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
        );
    }
}
