<?php

namespace App\Http\Controllers\Web;

use App\Exports\AbsensiExport;
use App\Exports\AttendanceSummary;
use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiPhoto;
use App\Models\DeviceSettings;
use App\Models\Permit;
use App\Models\Personel;
use App\Models\WorkPersonel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
            ->whereHas('Personel', function ($query) {
                $query->where('m_personel_status', 1);
            })
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

            $absensi->id_m_personel = $request['absensi']['personel'];
            $absensi->t_absensi_isLate = $request['absensi']['isLate'];
            $absensi->t_absensi_Dates = $request['absensi']['startDate'];
            $absensi->t_absensi_startClock = $request['absensi']['startClock'] != null ? $request['absensi']['startDate'] . ' ' . $request['absensi']['startClock'] : null;
            $absensi->t_absensi_endClock = $request['absensi']['endClock'] != null ? $request['absensi']['endDate'] . ' ' . $request['absensi']['endClock'] : null;
            $absensi->t_absensi_status = isset($request['absensi']['status']) && $request['absensi']['status'] ? 2 : 1;
            $absensi->t_absensi_catatan_telat_masuk = $request['absensi']['catatan_masuk'] != null ? $request['absensi']['catatan_masuk'] : null;
            $absensi->t_absensi_catatan = $request['absensi']['catatan_pulang'] != null ? $request['absensi']['catatan_pulang'] : null;
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
            $personels = Personel::has(
                'WorkPersonel'
            )->where('m_personel_status', 1)->where('id_m_user_company', $auth->id_m_user_company);

            if (isset($request->departemen) && $request->departemen != null) {
                $personels->where('id_m_departemen', $request->departemen);
            }
            $personels = $personels->get();
            foreach ($personels as $personel) {
                $start = new Carbon($personel->WorkPersonel->m_work_personel_time);
                if (strtotime($request->startDate) > strtotime($personel->WorkPersonel->m_work_personel_time)) {
                    $start = new Carbon($request->startDate);
                }
                $end = new Carbon($request->endDate);
                $period = CarbonPeriod::create($start, $end);

                $filter_date = [];

                foreach ($period as $date) {
                    $filter_date[] = $date->format('Y-m-d');
                }

                $absensis = Absensi::whereDate('t_absensi_Dates', '>=', $request->startDate)
                    ->whereDate('t_absensi_Dates', '<=', $request->endDate)
                    // ->whereNotNull('t_absensi_endClock')
                    ->where('id_m_personel', $personel->id_m_personel)
                    ->whereIn('t_absensi_status', [1, 2])
                    ->get();
                $kehadiran = 0;
                $terlambat = 0;
                $tidak_terlambat = 0;
                $wfh = 0;
                $total_jam = null;
                $total_cuti = 0;
                $cuti = Permit::where([
                    'permit_type' => 3,
                    'permit_status' => 1,
                    'id_m_personel' => $personel->id_m_personel
                ])->with('PermitDate')->get();

                foreach ($cuti as $ct) {
                    foreach ($ct->PermitDate as $pd) {
                        if (date('Y-m-d', strtotime($pd->permit_date)) >= $request->startDate && date('Y-m-d', strtotime($pd->permit_date)) <= $request->endDate) {
                            $total_cuti += 1;
                        }
                    }
                }

                foreach ($absensis as $absensi) {
                    $filter_date = Arr::where($filter_date, function ($row) use ($absensi) {
                        return $row != $absensi->t_absensi_Dates;
                    });

                    ++$kehadiran;
                    $datang = strtotime($absensi->t_absensi_startClock);
                    if ($absensi->t_absensi_endClock == null) {
                        $pulang = strtotime(date('Y-m-d H:i:s'));
                    } else {
                        $pulang = strtotime($absensi->t_absensi_endClock);
                    }
                    $jam_kerja = floor(($pulang - $datang) / 3600);
                    $total_jam = $total_jam + $jam_kerja;
                    if ($absensi->t_absensi_isLate == 2) {
                        $terlambat += 1;
                    } else {
                        $tidak_terlambat += 1;
                    }

                    if ($absensi->t_absensi_status == 2) $wfh += 1;
                }

                $personel->kehadiran = $kehadiran;
                $personel->terlambat = $terlambat;
                $personel->wfh = $wfh;
                $personel->tidak_terlambat = $tidak_terlambat;
                $personel->tidak_hadir = count($filter_date);
                $personel->total_jam = $total_jam;
                $personel->total_cuti = $total_cuti;
            }

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                Fungsi::MES_SUCCESS,
                $personels->load('Departemen')
            );
        }
    }

    public function attendanceSummaryDetail(Request $request)
    {
        $detail = Absensi::select('*')
            ->whereDate('t_absensi_Dates', '>=', $request->startDate)
            ->whereDate('t_absensi_Dates', '<=', $request->endDate)
            // ->whereNotNull('t_absensi_endClock')
            ->where('id_m_personel', $request->id_m_personel)
            ->whereIn('t_absensi_status', [1, 2]);

        if (strtolower($request->type) == 'terlambat') {
            $detail->where('t_absensi_isLate', 2);
        } else if (strtolower($request->type) == 'tidak terlambat') {
            $detail->where('t_absensi_isLate', '!=', 2);
        } else if (strtolower($request->type) == 'wfh') {
            $detail->where('t_absensi_status', 2);
        } else if (strtolower($request->type) == 'tidak absen') {
            $personel = Personel::find($request->id_m_personel);
            $start = new Carbon($personel->WorkPersonel->m_work_personel_time);
            if (strtotime($request->startDate) > strtotime($personel->WorkPersonel->m_work_personel_time)) {
                $start = new Carbon($request->startDate);
            }
            $end = new Carbon($request->endDate);
            $period = CarbonPeriod::create($start, $end);

            $filter_date = [];

            foreach ($period as $date) {
                $filter_date[] = $date->format('Y-m-d');
            }

            foreach ($detail->get() as $val) {
                $filter_date = Arr::where($filter_date, function ($row) use ($val) {
                    return $row != $val->t_absensi_Dates;
                });
            }

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                Fungsi::MES_SUCCESS,
                array_values($filter_date)
                // count($period->toArray())
                // $detail->get()->pluck('t_absensi_Dates')
            );
        } else if (strtolower($request->type) == 'cuti') {
            $detail = \App\Models\PermitDate::select('permit_date')
                ->whereDate('permit_date', '>=', $request->startDate)
                ->whereDate('permit_date', '<=', $request->endDate)
                ->whereHas('Permit', function ($query) use ($request) {
                    $query->where('permit_status', 1);
                    $query->where('id_m_personel', $request->id_m_personel);
                    $query->where('permit_type', 3);
                });
        }

        $filter_date = [];
        foreach ($detail->get() as $row) {
            $filter_date[] = $row->permit_date != null ? $row->permit_date : $row->t_absensi_Dates;
        }
        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $filter_date
        );
    }

    public function ExportExcel(Request $request)
    {
        try {

            $auth = Auth::user();
            $name = Auth::user()->name;
            // dd($auth);
            $absensi = Absensi::with([
                'Personel' => function ($query) {
                    $query->with('Departemen');
                },
                'WorkPersonel' => function ($query) {
                    $query->with(['getWorkPattern']);
                }
            ])->where('id_m_user_company', $auth->id_m_user_company)
                ->whereHas('Personel', function ($query) {
                    $query->where('m_personel_status', 1);
                })
                ->has('WorkPersonel')->whereIn('t_absensi_status', [1, 2])->orderBy('t_absensi_startClock');

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
                'absensi' => $absensi->get(),
                'denda' => DeviceSettings::select('m_device_settings_denda')->where('id_m_user_company', Auth::user()->id_m_user_company)->first()
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

    public function ExportExcelAttendanceSummary(Request $request)
    {
        try {

            $name = Auth::user()->name;
            $auth = Auth::user();
            $personels = Personel::has(
                'WorkPersonel'
            )->where('id_m_user_company', $auth->id_m_user_company);

            if (isset($request->departemen) && $request->departemen != null) {
                $personels->where('id_m_departemen', $request->departemen);
            }
            $personels = $personels->get();
            foreach ($personels as $personel) {
                $start = new Carbon($personel->WorkPersonel->m_work_personel_time);
                if (strtotime($request->start_date) > strtotime($personel->WorkPersonel->m_work_personel_time)) {
                    $start = new Carbon($request->start_date);
                }
                $end = new Carbon($request->end_date);
                $period = CarbonPeriod::create($start, $end);

                $filter_date = [];

                foreach ($period as $date) {
                    $filter_date[] = $date->format('Y-m-d');
                }

                $absensis = Absensi::whereBetween('t_absensi_Dates', [$request->start_date, $request->end_date])
                    ->whereNotNull('t_absensi_endClock')
                    ->where('id_m_personel', $personel->id_m_personel)
                    ->whereIn('t_absensi_status', [1, 2])
                    ->get();
                $kehadiran = 0;
                $terlambat = 0;
                $tidak_terlambat = 0;
                $wfh = 0;
                $total_jam = null;
                foreach ($absensis as $absensi) {
                    $filter_date = Arr::where($filter_date, function ($row) use ($absensi) {
                        return $row != $absensi->t_absensi_Dates;
                    });

                    ++$kehadiran;
                    $datang = strtotime($absensi->t_absensi_startClock);
                    $pulang = strtotime($absensi->t_absensi_endClock);
                    $jam_kerja = floor(($pulang - $datang) / 3600);
                    $total_jam = $total_jam + $jam_kerja;
                    if ($absensi->t_absensi_isLate == 2) {
                        $terlambat += 1;
                    } else {
                        $tidak_terlambat += 1;
                    }

                    if ($absensi->t_absensi_status == 2) $wfh += 1;
                }
                $personel->kehadiran = $kehadiran;
                $personel->terlambat = $terlambat;
                $personel->wfh = $wfh;
                $personel->tidak_terlambat = $tidak_terlambat;
                $personel->tidak_hadir = count($filter_date);
                $personel->total_jam = $total_jam;
            }

            $start = date('d-m-Y', strtotime($request->start_date));
            $end = date('d-m-Y', strtotime($request->end_date));

            if ($personels->count() < 1) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $data = [
                'attendance' => $personels,
            ];

            $url = "Laporan Ringkasan Kehadiran $name ($start ~ $end).xlsx";
            $excel = Excel::store(new AttendanceSummary($data), $url, 'excel', null, [
                'visibility' => 'public',
            ]);

            return response()->json([
                'url' => url('excel/' . $url),
                'message' => 'Data Ditemukan',
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 401
            ]);
            throw $e;
        }
    }
}
