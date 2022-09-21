<?php

namespace App\Http\Controllers\Web;

use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\UserCompany;
use App\Models\DeviceSettings;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeviceSettingsController extends Controller
{

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {

            $auth = Auth::user();

            if ($auth->id_m_user_company != null) {
                if (DeviceSettings::where('id_m_user_company', $auth->id_m_user_company)->count() < 1) {
                    $setting = new DeviceSettings();
                    $setting->id_m_user_company = $auth->id_m_user_company;
                    $setting->m_device_settings_absensiCamera = false;
                    $setting->m_device_settings_absensiFaceRecognition = false;
                    $setting->m_device_settings_visitCamera = false;
                    $setting->m_device_settings_visitFaceRecognition = false;
                    $setting->m_device_settings_overtimeCamera = false;
                    $setting->m_device_settings_overtimeFaceRecognition = false;
                    $setting->save();
                }
            }
            DB::commit();

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                "Sukses"
            );
        } catch (\Exception $e) {
            throw $e;
            DB::rollback();
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                Fungsi::MES_CREATE_EDIT
            );
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {

            $auth = Auth::user();

            $setting = DeviceSettings::where('id_m_user_company', $auth->id_m_user_company)->first();
            $setting->m_device_settings_absensiCamera = $request['device_settings']['m_device_settings_absensiCamera'];
            $setting->m_device_settings_absensiFaceRecognition = $request['device_settings']['m_device_settings_absensiFaceRecognition'];
            $setting->m_device_settings_visitCamera = $request['device_settings']['m_device_settings_visitCamera'];
            $setting->m_device_settings_visitFaceRecognition = $request['device_settings']['m_device_settings_visitFaceRecognition'];
            $setting->m_device_settings_overtimeCamera = $request['device_settings']['m_device_settings_overtimeCamera'];
            $setting->m_device_settings_overtimeFaceRecognition = $request['device_settings']['m_device_settings_overtimeFaceRecognition'];
            $setting->save();

            DB::commit();

            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                "Sukses"
            );
        } catch (\Exception $e) {
            throw $e;
            DB::rollback();
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                Fungsi::MES_CREATE_EDIT
            );
        }
    }

    public function detail()
    {
        $auth = Auth::user();
        $device_settings = DeviceSettings::where('id_m_user_company', $auth->id_m_user_company)->first();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $device_settings
        );
    }

    public function delete($id)
    {
        $message = 'Berhasil menghapus User Company';

        $user_company = UserCompany::findOrFail($id);
        $user_company->delete();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
        );
    }
}
