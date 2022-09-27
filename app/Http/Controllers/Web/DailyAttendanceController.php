<?php

namespace App\Http\Controllers\Web;

use App\Exports\AbsensiExport;
use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiPhoto;
use App\Models\Personel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DailyAttendanceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->t_absensi_Dates == null) {
            $t_absensi_Dates = Carbon::now()->format('Y-m-d');
        } else {
            $t_absensi_Dates = $request->t_absensi_Dates;
        }

        $auth = Auth::user();
        $absensi = Absensi::where('id_m_user_company', $auth->id_m_user_company)
            ->whereIn('t_absensi_status', [1, 2])
            ->has('WorkPersonel')
            ->with('PhotoAbsensi')
            ->with('Personel')
            ->with(['WorkPersonel' => function ($query) {
                $query->with(['getWorkPattern', 'getWorkSchedule']);
            }])->orderBy('t_absensi_startClock');

        if ($request->startDate != null && $request->endDate != null) {
            $absensi->whereBetween('t_absensi_Dates', [$request->startDate, $request->endDate]);
        } else {
            $absensi->where('t_absensi_Dates', 'ILIKE', $t_absensi_Dates);
        }

        $absensi = $absensi->get();

        foreach ($absensi as $val) {
            $startDate = \Carbon\Carbon::parse($val->t_absensi_startClock);
            $endDate = \Carbon\Carbon::parse($val->t_absensi_endClock);
            $val->t_absensi_startClock = $startDate->format('H:i:s');
            $val->t_absensi_startDate = $startDate->format('Y-m-d');
            if ($val->t_absensi_endClock != null) {
                $val->t_absensi_endClock = $endDate->format('H:i:s');
                $val->t_absensi_endDate = $endDate->format('Y-m-d');
            } else {
                $val->t_absensi_endClock = null;
                $val->t_absensi_endDate = null;
            }
        }

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $absensi->load(['Personel', 'PhotoAbsensi'])
        );
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info($request);
            $auth = Auth::user();
            $message = 'Berhasil menambahkan Absensi';

            if ($request->id != null) {
                $message = 'Berhasil memperbarui Absensi';
                $absensi = Absensi::findOrFail($request->id);
            } else {
                $absensi = new Absensi();
                $absensi->created_at = Carbon::now();
                $absensi->id_m_user_company = $auth->id_m_user_company;
            }

            $absensi->t_absensi_isLate = $request['absensi']['isLate'];
            $absensi->t_absensi_startClock = $request['absensi']['startClock'] != null ? $request['absensi']['startDate'] . ' ' . $request['absensi']['startClock'] : null;
            $absensi->t_absensi_endClock = $request['absensi']['endClock'] != null ? $request['absensi']['endDate'] . ' ' . $request['absensi']['endClock'] : null;
            $absensi->t_absensi_status = isset($request['absensi']['status']) && $request['absensi']['status'] ? 2 : 1;
            $absensi->updated_at = Carbon::now();
            $absensi->save();

            DB::commit();

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message
            );
        } catch (\Exception $th) {
            Log::info($th);
            throw $th;
            DB::rollback();
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                Fungsi::MES_CREATE_EDIT,
            );
        }
    }

    public function show($id)
    {
        $absensi = Absensi::findOrFail($id);

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $absensi
        );
    }

    public function delete($id)
    {
        $message = 'Berhasil menghapus Data Absensi';

        $absensi = Absensi::findOrFail($id);
        $absensiphoto = AbsensiPhoto::where('id_t_absensi', $id)->get();
        if ($absensiphoto != null) {
            foreach ($absensiphoto as $val) {
                $val->delete();
            }
        }
        $absensi->delete();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
        );
    }

    public function attendanceSummary(Request $request)
    {

        if ($request->startDate != null && $request->endDate != null) {
            if ($request->startDate != null) {
                if (Carbon::parse($request->endDate)->format('Y-m-d') < Carbon::parse($request->startDate)->format('Y-m-d')) {
                    DB::rollback();
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Tanggal end Date tidak boleh kurang dari start Date"
                    );
                }
            }

            $auth = Auth::user();
            $personels = Personel::where('id_m_user_company', $auth->id_m_user_company)->get();
            foreach ($personels as $personel) {
                $absensis = Absensi::whereBetween('t_absensi_Dates', [$request->startDate, $request->endDate])->whereNotNull('t_absensi_endClock')->where('id_m_personel', $personel->id_m_personel)->get();
                $kehadiran = 0;
                $total_jam = null;
                foreach ($absensis as $absensi) {
                    ++$kehadiran;
                    $datang = strtotime($absensi->t_absensi_startClock);
                    $pulang = strtotime($absensi->t_absensi_endClock);
                    $jam_kerja = floor(($pulang - $datang) / 3600);
                    $total_jam = $total_jam + $jam_kerja;
                }
                $personel->kehadiran = $kehadiran;
                $personel->total_jam = $total_jam;
            }

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                Fungsi::MES_SUCCESS,
                $personels
            );
        }
    }

    public function ExportExcel(Request $request)
    {
        try {

            $name = Auth::user()->name;
            // dd($auth);
            $absensi = Absensi::with([
                'Personel' => function ($query) {
                    $query->with('Departemen');
                },
                'WorkPersonel' => function ($query) {
                    $query->with(['getWorkPattern']);
                }
            ])->has('WorkPersonel')->whereIn('t_absensi_status', [1, 2])->orderBy('t_absensi_startClock');

            if (isset($request->start_date) && isset($request->end_date)) {
                $absensi->where('t_absensi_Dates', '>=', $request->start_date);
                $absensi->where('t_absensi_Dates', '<=', $request->end_date);
            }

            $start = date('d-m-Y', strtotime($request->start_date));
            $end = date('d-m-Y', strtotime($request->end_date));

            if ($absensi->count() < 1) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $data = [
                'absensi' => $absensi->get()
            ];

            $url = "Laporan Absensi $name ($start ~ $end).xlsx";
            $excel = Excel::store(new AbsensiExport($data), $url, 'excel', null, [
                'visibility' => 'public',
            ]);

            return response()->json([
                'url' => url('excel/' . $url),
                'message' => 'Data Ditemukan',
                'status' => 200
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
