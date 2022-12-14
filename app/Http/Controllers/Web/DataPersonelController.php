<?php

namespace App\Http\Controllers\Web;

use App\Fungsi;
use App\Http\Controllers\Controller;
use App\Models\Personel;
use App\Models\UserCompany;
use App\Models\WorkPersonel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DataPersonelController extends Controller
{
    public function index(Request $request)
    {
        $auth = Auth::user();

        $personels = Personel::with('WorkPersonel')->orderBy('id_m_personel', 'desc')->where('id_m_user_company', $auth->id_m_user_company)->with('Departemen');

        if (isset($request->work_personel) && $request->work_personel) {
            $personels->has('WorkPersonel');
        }
        if (isset($request->departemen) && $request->departemen) {
            $personels->where('id_m_departemen', $request->departemen);
        }
        if (!isset($request->isIndex)) {
            $personels->where('m_personel_status', 1);
        }
        if (isset($request->work_patern) && $request->work_patern) {
            $personels->whereHas('WorkPersonel', function ($query) use ($request) {
                $query->where('id_m_work_patern', $request->work_patern);
            });
        }

        if (isset($request->search) && $request->search != null) {
            $personels->where(function ($query) use ($request) {
                $query->where('username', 'LIKE', "%$request->search%");
                // $query->orWhere('m_personel_personID', 'ILIKE', "%$request->search%");
                // $query->orWhere('username', 'ILIKE', "%$request->search%");
                // $query->orWhere('m_personel_email', 'ILIKE', "%$request->search%");
                // $query->orWhere('device_id', 'ILIKE', "%$request->search%");
            });
        }


        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $personels->paginate($request->show ?? 10)
        );
    }

    public function datatable_get(Request $request)
    {
        $auth = Auth::user();
        $draw = request('draw');
        $start = request('start');
        $length = request('length');
        $search = request('search');
        $columns = request('columns');
        $order = request('order');

        $personels = Personel::query()->orderBy('id_m_personel', 'desc')->where('id_m_user_company', $auth->id_m_user_company);

        if (isset($request->work_personel) && $request->work_personel) {
            $personels->has('WorkPersonel');
        }
        if (isset($request->departemen) && $request->departemen) {
            $personels->where('id_m_departemen', $request->departemen);
        }
        if (isset($request->work_patern) && $request->work_patern) {
            $personels->whereHas('WorkPersonel', function ($query) use ($request) {
                $query->where('id_m_work_patern', $request->work_patern);
            });
        }

        $recordsTotal = $personels->count('id_m_personel');

        $recordsFiltered = 0;
        if ($search) {
            $search = $search['value'];
            $firstColumn = true;
            foreach ($columns as $column) {
                if ($column['searchable'] === 'true') {
                    if ($firstColumn) {
                        $personels->where($column['data'], 'LIKE', "%{$search}%");
                        $firstColumn = false;
                    } else {
                        $personels->orWhere($column['data'], 'LIKE', "%{$search}%");
                    }
                }
            }
            $recordsFiltered = $personels->count('id_m_personel');
        } else {
            $recordsFiltered = $recordsTotal;
        }

        // if ($columns[$order['column']]['orderable'] == 'true') {
        //     $personels->orderBy($columns[$order['column']]['data'], $order['dir']);
        // }

        $personels->skip($start);
        $personels->limit($length);
        $personels = $personels->get();

        foreach ($personels as $index => $row) {
            $personels[$index]['nomer'] = $index + 1;
            $personels[$index]['m_personel_status'] = '<button style="cursor: pointer;" @click="changeStatus(personel)" class="' . $row->m_personel_status == 1 ? 'btn btn-success btn-sm' : 'btn btn-danger btn-sm' . '">' . $row->m_personel_status == 1 ? 'On' : 'Off' . '</button>';
            $personels[$index]['button'] = "Belum Ada Aksi";
        }
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $personels
        ], 200);
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $auth = Auth::user();
            $company = UserCompany::findOrFail($auth->id_m_user_company);

            $message = 'Berhasil menambahkan Karyawan';

            $personel_exists = Personel::where('id_m_user_company', $auth->id_m_user_company)->where('m_personel_personID', $request['data_personel']['m_personel_personID']);
            if ($request->id != null) {
                $personel_exists->where('id_m_personel', '!=', $request->id);
            }
            $personel_exists = $personel_exists->first();
            if ($personel_exists != null) {
                DB::rollback();
                return $this->sendResponse(
                    Fungsi::STATUS_ERROR,
                    "Gagal, personel ID sudah digunakan"
                );
            }

            if ($request->id != null) {
                $message = 'Berhasil memperbarui Karyawan';
                $personel = Personel::findOrFail($request->id);
                $personel_exists = Personel::where('username', $request['data_personel']['username'])->where('id_m_personel', '!=', $request->id)->first();
                if ($personel_exists != null) {
                    DB::rollback();
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Gagal, username sudah digunakan"
                    );
                }
            } else {
                $count_personel = Personel::orderBy('id_m_personel', 'desc')->where('id_m_user_company', $auth->id_m_user_company)->count();
                $personel_exists = Personel::where('username', $request['data_personel']['username'])->first();
                if ($personel_exists != null) {
                    DB::rollback();
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Gagal, username sudah digunakan"
                    );
                }
                if (++$count_personel > $company->m_user_company_total_personel) {
                    DB::rollback();
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Gagal, jumlah karyawan lebih dari total karyawan perusahaan saat di masukan."
                    );
                }

                $personel = new Personel();
                $personel->created_at = Carbon::now();
                $personel->id_m_user_company = $auth->id_m_user_company;
            }

            $personel->m_personel_names = $request['data_personel']['m_personel_names'];
            $personel->m_personel_personID = $request['data_personel']['m_personel_personID'];
            $personel->username = $request['data_personel']['username'];
            $personel->m_personel_gender = $request['data_personel']['m_personel_gender'];
            $personel->m_personel_email = $request['data_personel']['m_personel_email'];
            // $personel->m_personel_status = $request['data_personel']['m_personel_status'];
            $personel->id_m_departemen = $request['data_personel']['id_m_departemen'];
            $personel->total_leave = $request['data_personel']['total_leave'];
            $personel->remaining_leave = $request['data_personel']['total_leave'] - $personel->remaining_leave;
            // $personel->remaining_leave = $personel->remaining_leave != null ? $personel->remaining_leave : $request['data_personel']['total_leave'];
            $personel->effective_date_leave = $request['data_personel']['effective_date_leave'];
            $personel->updated_at = Carbon::now();
            $personel->save();

            DB::commit();
            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message
            );
        } catch (\Exception $th) {
            DB::rollback();
            // throw $th;
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                Fungsi::MES_CREATE_EDIT,
            );
        }
    }

    public function show($id)
    {
        $data_personel = Personel::findOrFail($id);

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $data_personel
        );
    }

    public function generateToken($id)
    {
        $message = 'Berhasil generate token Data Personel';

        $length_token = 8;
        $generate_token = $this->generateRandomString($length_token);

        $personel = Personel::findOrFail($id);
        $personel->m_personel_tokenLogin = $generate_token;
        $personel->is_logged_in = null;
        $personel->save();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
                . ' Nama ' . $personel->m_personel_names
                . ' Person ID ' . $personel->m_personel_personID
        );
    }

    public function generatePassword($id)
    {
        $message = 'Berhasil generate password Data Personel';

        $length_token = 8;
        $generate_password = $this->generateRandomString($length_token);

        $personel = Personel::findOrFail($id);
        $personel->password = Hash::make($generate_password);
        $personel->m_personel_password_show = $generate_password;
        $personel->is_logged_in = null;
        $personel->save();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
                . ' Nama ' . $personel->m_personel_names
                . ' Person ID ' . $personel->m_personel_personID,
            $personel
        );
    }

    public function generateRandomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function resetDeviceId($id)
    {
        $message = 'Berhasil reset DeviceID Data Personel';

        $personel = Personel::findOrFail($id);
        $personel->device_id = null;
        $personel->is_logged_in = null;
        $personel->time_borrow_account = null;
        $personel->save();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
                . ' Nama ' . $personel->m_personel_names
                . ' Person ID ' . $personel->m_personel_personID
        );
    }

    public function changeStatus($id)
    {
        $message = 'Berhasil ubah status Data Personel';

        $personel = Personel::findOrFail($id);
        $personel->m_personel_status = $personel->m_personel_status == 1 ? 2 : 1;
        $personel->save();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
                . ' Nama ' . $personel->m_personel_names
                . ' Person ID ' . $personel->m_personel_personID
        );
    }

    public function delete($id)
    {
        $message = 'Berhasil menghapus Data Personel';

        $personel = Personel::find($id);
        $get_work_patterns = WorkPersonel::where('id_m_personel', $id)->get();

        if (count($get_work_patterns) != null) {
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                "Data  $personel->m_personel_names tidak bisa dihapus karena terkait dengan data lainnya !"
            );
        }

        $personel->delete();

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            $message
                . ' Nama ' . $personel->m_personel_names
                . ' Person ID ' . $personel->m_personel_personID
        );
    }
}
