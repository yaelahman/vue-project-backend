<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fungsi extends Model
{
    const MES_SUCCESS = "success";
    const MES_ERROR = "error";
    const MES_INFO = "info";
    const MES_CREATE_EDIT = "Gagal di lakukan. Mohon cek kembali";
    const MES_UPLOAD = "Format excel yang di uploud salah. Mohon cek kembali";

    const STATUS_SUCCESS = 200;
    const STATUS_ERROR = 400;

    public static function WhereQuery($request, $except = null)
    {
        $where_array = [];
        foreach ($request->keys() as $val) {
            if (isset($request[$val]) && $val != 'page' && $request[$val] != '') {
                if (isset($except) && in_array($val, $except)) {
                    continue;
                }
                $where_array[] = [$val, 'ILIKE', "%{$request[$val]}%"];
            }
        }
        return $where_array;
    }
}
