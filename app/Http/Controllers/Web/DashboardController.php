<?php

namespace App\Http\Controllers\Web;

use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\UserCompany;
use App\Models\Absensi;
use App\Models\CompanyIndustri;
use App\Models\Permit;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function checkAbsen(Request $request)
    {
        $auth = Auth::user();
        $personel = Personel::where('id_m_user_company', $auth->id_m_user_company);

        $absen = 0;
        $notAbsen = 0;
        $wfh = 0;
        $idPersonel = [];
        foreach ($personel->get() as $row) {
            $getData = Absensi::where('id_m_personel', $row->id_m_personel)
                ->where('t_absensi_Dates', date('Y-m-d'))
                ->whereIn('t_absensi_status', [1, 2])
                ->orderBy('id_t_absensi', 'desc')
                ->first();

            if ($getData != null) {
                if ($getData->t_absensi_status == 2) {
                    $wfh++;
                } else if ($getData->t_absensi_status == 1) {
                    $absen++;
                }
                array_push($idPersonel, $getData->id_m_personel);
            } else {
                $notAbsen++;
            }
        }

        $aktivitasPersonel = Absensi::whereIn('id_m_personel', $idPersonel)
            ->where('t_absensi_Dates', date('Y-m-d'))
            ->with('Personel')
            ->orderBy('id_t_absensi', 'desc')
            ->whereIn('t_absensi_status', [1, 2])
            ->limit(5)
            ->get();

        $data = [
            'idPersonel' => $idPersonel,
            'sudahAbsen' => $absen,
            'belumAbsen' => $notAbsen,
            'wfh'        => $wfh,
            'aktivitasPersonel' => $aktivitasPersonel->load('Personel')
        ];

        return $this->send_response($data);
    }

    public function chart()
    {
        $auth = Auth::user();
        $month = Absensi::where('id_m_user_company', $auth->id_m_user_company)
            ->whereYear('created_at', date('Y'))
            ->whereIn('t_absensi_status', [1, 2, 3, 4])
            ->select(DB::raw("to_char(created_at, 'MM') as month"))
            ->groupBy('month')
            ->get()
            ->pluck('month');

        $fixMonth = [];
        foreach ($month as $row) {
            $replacedMonth = $this->replaceToMonthFull("$row");
            array_push($fixMonth, $replacedMonth);
        }

        $absen = Absensi::where('id_m_user_company', $auth->id_m_user_company)
            ->whereYear('created_at', date('Y'))
            ->select(DB::raw("to_char(created_at, 'MM') as month, COUNT(id_t_absensi) as count"))
            ->groupBy('month')
            ->where('t_absensi_status', 1)->get()->pluck('count');

        $wfh = Absensi::where('id_m_user_company', $auth->id_m_user_company)
            ->whereYear('created_at', date('Y'))
            ->select(DB::raw("to_char(created_at, 'MM') as month, COUNT(id_t_absensi) as count"))
            ->groupBy('month')
            ->where('t_absensi_status', 2)->get()->pluck('count');

        $lembur = Absensi::where('id_m_user_company', $auth->id_m_user_company)
            ->whereYear('created_at', date('Y'))
            ->select(DB::raw("to_char(created_at, 'MM') as month, COUNT(id_t_absensi) as count"))
            ->groupBy('month')
            ->where('t_absensi_status', 3)->get()->pluck('count');

        $visit = Absensi::where('id_m_user_company', $auth->id_m_user_company)
            ->whereYear('created_at', date('Y'))
            ->select(DB::raw("to_char(created_at, 'MM') as month, COUNT(id_t_absensi) as count"))
            ->groupBy('month')
            ->where('t_absensi_status', 4)->get()->pluck('count');


        $data = [
            'month' => $fixMonth,
            'absen'   => $absen,
            'wfh'   => $wfh,
            'lembur'   => $lembur,
            'visit'   => $visit,
        ];

        return $this->send_response($data);
    }

    public function chart2()
    {
        $auth = Auth::user();
        $month = Absensi::where('id_m_user_company', $auth->id_m_user_company)
            ->whereYear('created_at', date('Y'))
            ->select(DB::raw("to_char(created_at, 'MM') as month"))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('month');

        $fixMonth = [];
        foreach ($month as $row) {
            $replacedMonth = $this->replaceToMonthFull("$row");
            array_push($fixMonth, $replacedMonth);
        }

        $tidak_terlambat = Absensi::where('id_m_user_company', $auth->id_m_user_company)
            ->whereYear('created_at', date('Y'))
            ->select(DB::raw("to_char(created_at, 'MM') as month, COUNT(id_t_absensi) as count"))
            ->groupBy('month')
            ->orderBy('month')
            ->where('t_absensi_isLate', '!=', 2)->get()->pluck('count');

        $terlambat = Absensi::where('id_m_user_company', $auth->id_m_user_company)
            ->whereYear('created_at', date('Y'))
            ->select(DB::raw("to_char(created_at, 'MM') as month, COUNT(id_t_absensi) as count"))
            ->groupBy('month')
            ->orderBy('month')
            ->where('t_absensi_isLate', 2)->get()->pluck('count');


        $data = [
            'month' => $fixMonth,
            'terlambat'   => $terlambat,
            'tidak_terlambat'   => $tidak_terlambat,
        ];

        return $this->send_response($data);
    }

    public function checkPersonelBelumAbsen()
    {
        $auth = Auth::user();

        $absensi = Absensi::where('t_absensi_Dates', date('Y-m-d'))
            ->whereIn('t_absensi_status', [1, 2])
            ->where('id_m_user_company', $auth->id_m_user_company)
            ->get()->pluck('id_m_personel');

        $personel = Personel::whereNotIn('id_m_personel', $absensi)
            ->where('id_m_user_company', $auth->id_m_user_company)
            ->with('Departemen')
            ->get();

        return $this->send_response($personel);
    }

    public function checkPersonelSudahAbsen()
    {
        $auth = Auth::user();

        $absensi = Absensi::where('t_absensi_Dates', date('Y-m-d'))
            ->whereIn('t_absensi_status', [1, 2])
            ->where('id_m_user_company', $auth->id_m_user_company)
            ->get()->pluck('id_m_personel');

        $personel = Personel::whereIn('m_personel.id_m_personel', $absensi)
            ->where('m_personel.id_m_user_company', $auth->id_m_user_company)
            ->join('m_departemen', 'm_departemen.id_m_departemen', '=', 'm_personel.id_m_departemen')
            ->join('t_absensi', 't_absensi.id_m_personel', '=', 'm_personel.id_m_personel')
            // ->with(['Departemen', 'Absensi' => function ($query) {
            //     $query->whereIn('t_absensi_status', [1, 2]);
            // }])
            ->whereDate('t_absensi.t_absensi_Dates', date('Y-m-d'))
            ->whereIn('t_absensi.t_absensi_status', [1, 2])
            ->orderBy('t_absensi.t_absensi_startClock', 'ASC')
            ->get();

        return $this->send_response($personel);
    }

    public function checkPersonelWFH()
    {
        $auth = Auth::user();

        $absensi = Absensi::where('t_absensi_Dates', date('Y-m-d'))
            ->whereIn('t_absensi_status', [2])
            ->where('id_m_user_company', $auth->id_m_user_company)
            ->get()->pluck('id_m_personel');

        $personel = Personel::whereIn('id_m_personel', $absensi)
            ->where('id_m_user_company', $auth->id_m_user_company)
            ->with(['Departemen', 'Absensi' => function ($query) {
                $query->whereIn('t_absensi_status', [2]);
            }])
            ->get();

        return $this->send_response($personel);
    }

    public function checkPersonelKunjungan()
    {
        $auth = Auth::user();

        $absensi = Absensi::where('t_absensi_Dates', date('Y-m-d'))
            ->with(['Personel' => function ($query) {
                $query->with('Departemen');
            }])
            ->whereIn('t_absensi_status', [4])
            ->where('id_m_user_company', $auth->id_m_user_company)
            ->get();

        // $personel = Personel::whereIn('id_m_personel', $absensi)
        //     ->where('id_m_user_company', $auth->id_m_user_company)
        //     ->with(['Departemen', 'Absensi' => function ($query) {
        //         $query->whereIn('t_absensi_status', [4]);
        //     }])
        //     ->get();

        return $this->send_response($absensi);
    }

    public function checkIzin()
    {
        $auth = Auth::user();

        $jam = Permit::whereDate('permit_startclock', date('Y-m-d'))
            ->whereHas('Personel', function ($query) use ($auth) {
                $query->where('id_m_user_company', $auth->id_m_user_company);
            })
            ->whereIn('permit_type', [1])
            ->where('permit_status', 1)
            ->get()->pluck('id_m_personel');

        $hari = Permit::whereHas('PermitDate', function($query) {
            $query->whereDate('permit_date', date('Y-m-d'));
        })
            ->whereHas('Personel', function ($query) use ($auth) {
                $query->where('id_m_user_company', $auth->id_m_user_company);
            })
            ->whereIn('permit_type', [2])
            ->where('permit_status', 1)
            ->get()->pluck('id_m_personel');

        $personel = Personel::whereIn('id_m_personel', $jam)
            ->where('id_m_user_company', $auth->id_m_user_company)
            ->with(['Departemen', 'Permit' => function ($query) {
                $query->whereIn('permit_type', [1]);
                $query->where('permit_status', 1);
                $query->whereDate('permit_startclock', date('Y-m-d'));
            }])
            ->get();
        $personel2 = Personel::whereIn('id_m_personel', $hari)
            ->where('id_m_user_company', $auth->id_m_user_company)
            ->with(['Departemen', 'Permit' => function ($query) {
                $query->whereIn('permit_type', [2]);
                $query->where('permit_status', 1);
                $query->whereHas('PermitDate', function($query) {
                    $query->whereDate('permit_date', date('Y-m-d'));
                });
            }])
            ->get();

        return $this->send_response([
            'jam' => $personel,
            'hari' => $personel2
        ]);
    }

    public function checkCuti()
    {
        $auth = Auth::user();

        $cuti = Permit::whereDate('created_at', date('Y-m-d'))
            ->whereHas('Personel', function ($query) use ($auth) {
                $query->where('id_m_user_company', $auth->id_m_user_company);
            })
            ->whereIn('permit_type', [3])
            ->where('permit_status', 1)
            ->get()->pluck('id_m_personel');

        $personel = Personel::whereIn('id_m_personel', $cuti)
            ->where('id_m_user_company', $auth->id_m_user_company)
            ->with(['Departemen', 'Permit' => function ($query) {
                $query->whereIn('permit_type', [3]);
                $query->where('permit_status', 1);
            }])
            ->get();

        return $this->send_response($personel);
    }

    public function countApproval()
    {
        $jam = Permit::where([
            'permit_status' => 0,
            'permit_type' => 1
        ])->count();
        $hari = Permit::where([
            'permit_status' => 0,
            'permit_type' => 2
        ])->count();
        $cuti = Permit::where([
            'permit_status' => 0,
            'permit_type' => 3
        ])->count();
        $lembur = Absensi::where([
            't_absensi_status_admin' => 0,
            't_absensi_status' => 3
        ])->count();

        return $this->send_response([
            'jam' => $jam,
            'hari' => $hari,
            'cuti' => $cuti,
            'lembur' => $lembur,
        ]);
    }

    public function replaceToMonthFull($param = 'param')
    {
        // $month = substr($param);
        switch ($param) {
            case 1:
                return 'Januari';
            case 2:
                return 'Februari';
            case 3:
                return 'Maret';
            case 4:
                return 'April';
            case 5:
                return 'Mei';
            case 6:
                return 'Juni';
            case 7:
                return 'Juli';
            case 8:
                return 'Agustus';
            case 9:
                return 'September';
            case 10:
                return 'Oktober';
            case 11:
                return 'November';
            case 12:
                return 'Desember';
        }
    }

    public function checkAkumulasiSuperadmin()
    {
        $company = UserCompany::where('id_m_user_company', '!=', null)->count();
        $personel = Personel::count();

        $month = UserCompany::whereYear('created_at', date('Y'))
            ->select(DB::raw("to_char(created_at, 'MM') as month"))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('month');

        // dd($month);
        $fixMonth = [];
        foreach ($month as $row) {
            $replacedMonth = $this->replaceToMonthFull("$row");
            array_push($fixMonth, $replacedMonth);
        }

        $companyCount = UserCompany::whereYear('created_at', date('Y'))
            ->select(DB::raw("to_char(created_at, 'MM') as month, COUNT(id_m_user_company) as count"))
            ->groupBy('month')
            ->orderBy('month')
            ->get()->pluck('count');

        $aktivitas = UserCompany::orderBy('id_m_user_company', 'desc')->limit(5)->get();

        $data = [
            'companyTerdaftar' => $company,
            'personelAktif' => $personel,
            'month' => $fixMonth,
            'company' => $companyCount,
            'aktivitas' => $aktivitas
        ];

        return $this->send_response($data);
    }
}
