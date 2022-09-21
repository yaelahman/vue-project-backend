<?php

namespace App\Http\Controllers\Web;

use App\Fungsi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('guest')->except('Logout');
    // }

    public function check()
    {
        // $user = Auth::user();
        // dd($user->assignRole('Super Admin'));
        $user = User::where('id', Auth::user()->id)->with('roles')->first();
        return $user;
    }

    public function getUserAuth()
    {
        $user = Auth::user();

        $permission = $user->getAllPermissions();
        $roles = $user->getRoleNames()->first();

        $data = [
            'user' => $user,
            'permission' => $permission,
            'role' => $roles,
        ];

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $data
        );
    }

    public function getUser()
    {
        $user = User::with('roles')->get();

        $data = [
            'user' => $user,
        ];

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $data
        );
    }

    public function editProfilUser(Request $request)
    {
        $message = null;
        if ($request->profil != null) {
            $profil = KaryawanAnper::findOrFail($request['profil']['nik_karyawan']);

            if ($request->get('image') == '') {
                $name = $request['profil']['foto_profil'];
            } else {
                $image_path = 'assets/img/karyawan/' . $request['profil']['foto_profil'];;

                if (file_exists($image_path)) {
                    @unlink($image_path);
                }

                $image = $request->get('image');
                $name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
                \Image::make($request->get('image'))->save(public_path('assets/img/karyawan/') . $name);
            }

            $profil->foto_profil = $name;
            $profil->save();
        }

        $user = User::findOrFail($request['user']['id_user']);
        $user->syncRoles($request['roles']);

        if (isset($request['user']['pwd_lama'])) {
            if (Hash::check($request['user']['pwd_lama'], $user->password)) {
                if ($request['user']['pwd_baru'] != $request['user']['pwd_confirm']) {
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Confirm password tidak sama",
                    );
                } else {
                    $user->fill(['password' => Hash::make($request['user']['pwd_baru'])]);
                    $user->save();

                    $message = "Berhasil update password";
                }
            } else {
                return $this->sendResponse(
                    Fungsi::STATUS_ERROR,
                    "Password lama tidak sesuai",
                );
            }
        }

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            "Berhasil update profil " . $message,
        );
    }

    public function updatePassword(Request $request)
    {

        $user = Auth::user();
        // return $user;

        $user = User::findOrFail($user->id);

        if (isset($request['old_password'])) {
            if (Hash::check($request['old_password'], $user->password)) {
                $number = preg_match('@[0-9]@', $request['new_password']);
                $uppercase = preg_match('@[A-Z]@', $request['new_password']);
                $lowercase = preg_match('@[a-z]@', $request['new_password']);
                $specialChars = preg_match('@[^\w]@', $request['new_password']);

                if (strlen($request['new_password']) < 8) {
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Password Minimal 8 Huruf",
                    );
                }
                if (!$number) {
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Password Harus memiliki angka",
                    );
                }
                if (!$uppercase) {
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Password Harus ada yang berupa kapital",
                    );
                }
                if ($request['new_password'] != $request['new_password_confirm']) {
                    return $this->sendResponse(
                        Fungsi::STATUS_ERROR,
                        "Confirm password tidak sama",
                    );
                } else {
                    $user->fill(['password' => Hash::make($request['new_password'])]);
                    $user->save();

                    $message = "Berhasil update password";
                }
            } else {
                return $this->sendResponse(
                    Fungsi::STATUS_ERROR,
                    "Password lama tidak sesuai",
                );
            }
        }

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            "Berhasil Mengubah Password " . $message,
        );
    }

    public function detailProfilUser($id)
    {
        $user = User::where('id', $id)->first();
        $user['roles'] = $user->getRoleNames();
        $response['user'] = $user;

        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $response
        );
    }
}
