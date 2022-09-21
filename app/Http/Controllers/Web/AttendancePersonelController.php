<?php

namespace App\Http\Controllers\Web;

use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\AttendancePersonel;
use App\Models\Personel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AttendancePersonelController extends Controller
{
    public function index($id)
    {
        $attendance_personels = AttendancePersonel::with('getPersonel')->where('id_m_attendance_spots', $id)->get();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $attendance_personels
        );
    }

    public function getDataPersonel($id)
    {
        $auth = Auth::user();
        $get_attendance_personels = AttendancePersonel::where('id_m_user_company', $auth->id_m_user_company)->get();
        $data = [];

        foreach($get_attendance_personels as $get_attendance_personel){
            $data['id_m_personel'] = array_push($data, $get_attendance_personel->id_m_personel);
        }

        $get_personel = Personel::where('id_m_user_company', $auth->id_m_user_company)->whereNotIn('id_m_personel', $data)->get();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $get_personel
        );
    }

    public function addPersonel(Request $request)
    {
        $auth = Auth::user();
        $message = 'Berhasil menambahkan Personel di Attendance Spot';

        foreach($request['selected'] as $val) {
            $attendance_personel = new AttendancePersonel();
            $attendance_personel->id_m_personel = $val;
            $attendance_personel->id_m_attendance_spots = $request->id;
            $attendance_personel->id_m_user_company = $auth->id_m_user_company;
            $attendance_personel->created_at = Carbon::now();
            $attendance_personel->updated_at = Carbon::now();
            $attendance_personel->save();
        }

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
        );
    }

    public function delete($id)
    {
        $message = 'Berhasil menghapus Personel di Attendance Personel';

        $build = AttendancePersonel::findOrFail($id);
        $build->delete();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
        );
    }
}
