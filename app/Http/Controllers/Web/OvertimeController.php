<?php

namespace App\Http\Controllers\Web;

use App\Exports\OvertimeExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Fungsi;
use App\Models\Absensi;
use App\Models\AbsensiPhoto;
use App\Models\Personel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeController extends Controller
{


    public function index(Request $request)
    {
        if ($request->t_absensi_Dates == null) {
            $t_absensi_Dates = Carbon::now()->format('Y-m-d');
        } else {
            $t_absensi_Dates = $request->t_absensi_Dates;
        }
        $absensi = Absensi::where('t_absensi_status', 3)
            // ->orderBy('id_t_absensi', 'desc')
            // ->has('WorkPersonel')
            ->where('id_m_user_company', $this->auth()->id_m_user_company)
            ->with('PhotoAbsensi')
            ->with('Personel')
            ->orderBy('t_absensi_startClock');

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

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            Log::info($request);
            $auth = Auth::user();
            $message = 'Berhasil menambahkan Lembur';

            if ($request->id != null) {
                $message = 'Berhasil memperbarui Lembur';
                $absensi = Absensi::findOrFail($request->id);
                $date = $absensi->t_absensi_Dates;
            } else {
                $absensi = new Absensi();
                $absensi->created_at = Carbon::now();
                $absensi->id_m_user_company = $auth->id_m_user_company;
                $date = date('Y-m-d');
            }
            $absensi->id_m_personel = $request['absensi']['personel'];
            $absensi->t_absensi_Dates = $date;
            $absensi->t_absensi_startClock = $request['absensi']['startClock'] != null ? $request['absensi']['startDate']. " " . $request['absensi']['startClock'] : null;
            $absensi->t_absensi_endClock = $request['absensi']['endClock'] != null ? $request['absensi']['endDate'] . " " . $request['absensi']['endClock'] : null;
            $absensi->t_absensi_status = 3;
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $message = 'Berhasil menghapus Data Lembur';

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

    public function ExportExcel(Request $request)
    {
        $name = Auth::user()->name;
        $absensi = Absensi::with([
            'Personel' => function ($query) {
                $query->with('Departemen');
            },
            'WorkPersonel' => function ($query) {
                $query->with(['getWorkPattern']);
            }
        ])
            // ->has('WorkPersonel')
            ->where('t_absensi_status', 3)->orderBy('t_absensi_startClock');

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

        $url = "Laporan Lembur $name ($start ~ $end).xlsx";
        $excel = Excel::store(new OvertimeExport($data), $url, 'excel', null, [
            'visibility' => 'public',
        ]);

        return response()->json([
            'url' => url('excel/' . $url),
            'message' => 'Data Ditemukan',
            'status' => 200
        ]);
    }
}
