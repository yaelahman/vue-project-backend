<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Absensi;
use App\Models\AbsensiPhoto;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function __construct()
    {
        $this->middleware('token.is.valid'); 
    }

    public function clockIn(Request $request){
        $validator = Validator::make($request->all(), [
            'absensi_date' => 'required|date',
            'absensi_startClock' => 'required|date',
            'photo' => 'required|mimes:jpg,bmp,png',
        ]);
    
        if ($validator->fails()) {
            return $this->fail_mandatory($validator->errors());
        }

        DB::beginTransaction();
        try{
            $personel = auth('api')->user();

            $absensi = new Absensi;
            $absensi->t_absensi_Dates       = $request->absensi_date;
            $absensi->t_absensi_startClock  = $request->absensi_startClock;
            $absensi->t_absensi_status      = Absensi::STATUS_CLOCKIN;
            $absensi->t_absensi_type        = Absensi::TEXT_CLOCKIN;
            $absensi->id_m_personel         = $personel->id_m_personel;
            $absensi->save();
    
            $photo_absensi = new AbsensiPhoto;
            $photo_absensi->t_absensi_photo_date        = date('Y-m-d H:i:s');
            $photo_absensi->id_t_absensi                = $absensi->id_t_absensi;
            
            $file = $request->file('photo');
            if($file){
                $ext  = $file->getClientOriginalExtension();
                $fileName = "absensi1_{$request->absensi_date}_{$personel->id_m_personel}.$ext";
                $file->move('storage/photo_absensi/', $fileName);
    
                $photo_absensi->t_absensi_photopath         = $fileName;
                $photo_absensi->t_absensi_photofileOri      = $fileName;
                $photo_absensi->t_absensi_photofileSystem   = $fileName;
            }            
            $photo_absensi->save();
            DB::commit();
            return $this->send_response();
        }catch(\Exception $e){
            DB::rollBack();
            return $this->send_response([ $e->getMessage() ], self::STATUS_INTERNAL_SERVER_ERROR, self::MESSAGE_ERROR);
        }
    }

    public function clockOut(Request $request){
        $validator = Validator::make($request->all(), [
            'absensi_date' => 'required|date',
            'absensi_endClock' => 'required|date',
        ]);
    
        if ($validator->fails()) {
            return $this->fail_mandatory($validator->errors());
        }

        DB::beginTransaction();
        try{
            $personel = auth('api')->user();

            $absensi = new Absensi;
            $absensi->t_absensi_Dates       = $request->absensi_date;
            $absensi->t_absensi_endClock    = $request->absensi_endClock;
            $absensi->t_absensi_status      = Absensi::STATUS_CLOCKOUT;
            $absensi->t_absensi_type        = Absensi::TEXT_CLOCKOUT;
            $absensi->id_m_personel         = $personel->id_m_personel;
            $absensi->save();    
            DB::commit();
            return $this->send_response();
        }catch(\Exception $e){
            DB::rollBack();
            return $this->send_response([ $e->getMessage() ], self::STATUS_INTERNAL_SERVER_ERROR, self::MESSAGE_ERROR);
        }
    }
}
