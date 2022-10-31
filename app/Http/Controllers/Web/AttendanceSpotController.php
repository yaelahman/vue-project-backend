<?php

namespace App\Http\Controllers\Web;

use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\AttendancePersonel;
use App\Models\AttendanceSpot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceSpotController extends Controller
{
    public function index()
    {
        $auth = Auth::user();
        $attendance_spots = AttendanceSpot::with('getUserCompany')->orderBy('id_m_attendance_spots', 'desc')
            ->where('id_m_user_company', $auth->id_m_user_company)
            ->get();

        foreach ($attendance_spots as $attendance_spot) {
            $attendance_spot->count_personel = AttendancePersonel::where('id_m_attendance_spots', $attendance_spot->id_m_attendance_spots)
                ->whereHas('getPersonel', function ($query) {
                    $query->where('m_personel_status', 1);
                })->count();
        }

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $attendance_spots
        );
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $auth = Auth::user();
            $message = 'Berhasil menambahkan Lokasi Kehadiran';

            if ($request->id != null) {
                $message = 'Berhasil memperbarui Lokasi Kehadiran';
                $build = AttendanceSpot::findOrFail($request->id);
            } else {
                $build = new AttendanceSpot();
                $build->id_m_user_company = $auth->id_m_user_company;
                $build->created_at = Carbon::now();
            }

            $build->m_attendance_spots_address = $request['attendance_spot']['m_attendance_spots_address'];
            $build->m_attendance_spots_latitude = $request['attendance_spot']['m_attendance_spots_latitude'];
            $build->m_attendance_spots_longitude = $request['attendance_spot']['m_attendance_spots_longitude'];
            $build->m_attendance_spots_name = $request['attendance_spot']['m_attendance_spots_name'];
            $build->m_attendance_spots_radius = $request['radius'];
            $build->updated_at = Carbon::now();
            $build->save();

            DB::commit();

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message
            );
        } catch (\Exception $th) {
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
        $attendance_spot = AttendanceSpot::findOrFail($id);

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $attendance_spot
        );
    }

    public function delete($id)
    {
        $message = 'Berhasil menghapus Attendance Spot';

        $build = AttendanceSpot::findOrFail($id);
        $build->delete();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
        );
    }
}
