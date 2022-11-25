<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkPattern;
use App\Models\WorkPatternDay;
use Illuminate\Database\Eloquent\Builder;
use PDO;
use Illuminate\Support\Facades\DB;
use App\Fungsi;
use App\Models\WorkPatern;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkPatternController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $auth = Auth::user();
        $work_pattern = WorkPatern::orderBy('id_m_work_patern', 'desc')
            ->withCount(['WorkPersonel' => function ($query) {
                $query->whereHas('getPersonel', function ($query) {
                    $query->where('m_personel_status', 1);
                });
            }])
            ->withCount(['WPDKerja' => function (Builder $query) {
                $query->where('m_work_schedule_type', '=', 1);
            }])
            ->withCount(['WPDLibur' => function (Builder $query) {
                $query->where('m_work_schedule_type', '=', 2);
            }])->where('id_m_user_company', $auth->id_m_user_company);


        if (isset($request->search) && $request->search != null) {
            $work_pattern->where(function ($query) use ($request) {
                $query->where('m_work_patern_name', 'ILIKE', "%$request->search%");
            });
        }

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $work_pattern->paginate($request->show ?? 10)
        );
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $auth = Auth::user();
            $message = 'Berhasil menambahkan Jadwal Kerja';

            if ($request->id != null) {
                $message = 'Berhasil memperbarui Jadwal Kerja';
                $work_pattern = WorkPatern::findOrFail($request->id);
            } else {
                $work_pattern = new WorkPatern();
                $work_pattern->created_at = Carbon::now();
                $work_pattern->id_m_user_company = $auth->id_m_user_company;
            }
            $work_pattern->m_work_patern_name = $request['work_pattern']['m_work_patern_name'];
            $work_pattern->m_work_patern_numberCycle = $request->m_work_patern_numberCycle;
            $work_pattern->m_work_patern_tolerance = $request['work_pattern']['m_work_patern_tolerance'];
            $work_pattern->updated_at = Carbon::now();

            if ($work_pattern->save()) {
                $id_work_schedule = WorkSchedule::where('id_m_work_patern', $work_pattern->id_m_work_patern)
                    ->orderBy('id_m_work_schedule', 'asc')
                    ->get()
                    ->pluck('id_m_work_schedule');

                $id_work_schedule_new = [];
                foreach ($request['work_schedule'] as $index => $row) {
                    $work_schedule = isset($id_work_schedule[$index]) ? WorkSchedule::find($id_work_schedule[$index]) : null;
                    if ($work_schedule == null) {
                        $work_schedule = new WorkSchedule();
                    }
                    $work_schedule->m_work_schedule_type = $row['m_work_schedule_type'];
                    $work_schedule->m_work_schedule_clockIn = $row['m_work_schedule_clockIn'] != null ? $row['m_work_schedule_clockIn'] : null;
                    $work_schedule->m_work_schedule_clockOut = $row['m_work_schedule_clockOut'] != null ? $row['m_work_schedule_clockOut'] : null;
                    $work_schedule->id_m_work_patern = $work_pattern->id_m_work_patern;
                    $work_schedule->save();

                    array_push($id_work_schedule_new, $work_schedule->id_m_work_schedule);
                }

                WorkSchedule::where('id_m_work_patern', $work_pattern->id_m_work_patern)
                    ->whereNotIn('id_m_work_schedule', $id_work_schedule_new)
                    ->delete();
            }

            DB::commit();

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message,
                $id_work_schedule_new
            );
        } catch (\Throwable $th) {
            throw $th;
            DB::rollback();
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                Fungsi::MES_CREATE_EDIT
            );
        }
    }

    public function detail($id)
    {
        $work_pattern = WorkPatern::with('getWorkSchedule')->findOrFail($id);

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $work_pattern
        );
    }

    public function delete($id)
    {
        $message = 'Berhasil menghapus Jadwal Kerja';

        try {
            $work_pattern = WorkPatern::findOrFail($id);
            $word_schedules = WorkSchedule::where('id_m_work_patern', $work_pattern->id_m_work_patern)->get();
            foreach ($word_schedules as $word_schedule) {
                $word_schedule->delete();
            }
            $work_pattern->delete();

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message
            );
        } catch (\Exception $e) {
            return $e;
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                "Gagal Menghapus Jadwal Kerja"
            );
        }
    }
}
