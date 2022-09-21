<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Controller;
use Log;
use Carbon\Carbon;
use App\Fungsi;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\RoleHasPermission;
use Auth;

class RoleController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function GetRole(Request $request)
    {
        $role = Role::where(Fungsi::WhereQuery($request));
        if (isset($request->page)) {
            $role = $role->paginate(10);
            $role->appends($request->only($request->keys()));
        } else {
            $role = $role->get();
        }
        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $role
        );
    }

    public function CreateRole(Request $request)
    {
        DB::beginTransaction();
        try {
            $message = 'Berahasil menambahkan Role';
            $new_role = new Role();
            if (isset($request->id)) {
                $new_role = Role::find($request->id);
                $message = 'Berahasil update Role';
            }
            $new_role->name = $request->name;
            $new_role->created_at = Carbon::now();
            $new_role->updated_at = Carbon::now();
            $new_role->save();
            //assign permission to role
            // $new_role->syncPermissions('view.create');
            DB::commit();
            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message
            );
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                Fungsi::MES_CREATE_EDIT
            );
        }
    }

    public function DeleteRole($id)
    {
        $role = Role::findOrFail($id);
        if ($role->delete()) {
            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                "Berhasil menghapus Role"
            );
        } else {
            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                "Gagal menghapus Role"
            );
        }
    }

    public function GetPermission(Request $request)
    {
        $permission = Permission::where(Fungsi::WhereQuery($request))->orderBy('name', 'asc');
        if (isset($request->page)) {
            $permission = $permission->paginate(10);
            $permission->appends($request->only($request->keys()));
        } else {
            $permission = $permission->get();
        }
        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $permission
        );
    }

    public function CreatePermission(Request $request)
    {
        DB::beginTransaction();
        $message = 'Berahasil menambahkan Permission';
        try {
            $new_permission = new Permission();
            if (isset($request->id)) {
                $new_permission = Permission::find($request->id);
                $message = 'Berahasil update Permission';
            }
            $new_permission->name = $request->name;
            $new_permission->created_at = Carbon::now();
            $new_permission->updated_at = Carbon::now();
            $new_permission->save();
            DB::commit();
            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                $message
            );
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendResponse(
                Fungsi::STATUS_ERROR,
                Fungsi::MES_CREATE_EDIT
            );
        }
    }

    public function DeletePermission($id)
    {
        $role = Permission::findOrFail($id);
        if ($role->delete()) {
            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                "Berhasil menghapus Permission"
            );
        } else {
            return $this->sendResponse(
                Fungsi::STATUS_SUCCESS,
                "Gagal menghapus Permission"
            );
        }
    }

    public function GetRolePermission(Request $request, $id)
    {
        $permission = Permission::where(Fungsi::WhereQuery($request))->orderBy('name', 'asc');
        if (isset($request->page)) {
            $permission = $permission->paginate(10);
            $permission->appends($request->only($request->keys()));
        } else {
            $permission = $permission->get();
        }
        $role = Role::find($id);
        $has_permission = DB::table('role_has_permissions')
            ->select('permissions.name')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('role_id', $id)->get();

        $response['permission'] = $permission;
        $response['role'] = $role;
        $response['has_permission'] = $has_permission;
        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $response
        );
    }

    public function SetPermission(Request $request)
    {
        $role = Role::find($request->role['id']); //AMBIL ROLE BERDASARKAN ID
        $role->syncPermissions($request->permissions); //SET PERMISSION UNTUK ROLE TERSEBUT
        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            'Berhasil set permission',
        );
    }

    public function SelfPermission()
    {
        $permissions = Auth::user()->getAllPermissions();
        return $this->sendResponse(
            Fungsi::STATUS_SUCCESS,
            Fungsi::MES_SUCCESS,
            $permissions
        );
    }
}
