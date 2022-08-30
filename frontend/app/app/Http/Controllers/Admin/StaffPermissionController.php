<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\StaffCategory;
use DB;
use Carbon\Carbon;
use DataTables;

class StaffPermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StaffCategory::where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                   $actionBtn = '<a href="'.@route('admin.permissions.edit-page',['role_id' => $row->role_id]).'"  class="btn btn-primary btn-sm">Edit Permission</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Permission List';
        return view('admin.permissions.permissions', $data);
    }

    public function edit(Request $request, $role_id)
    {
        $data['staff_category'] = StaffCategory::where('role_id', $role_id)->first();
        if(is_null($data['staff_category'])){
            return redirect()->route('admin.permissions.index-page')->with('error','Role not found');
        }
        $data['permissions'] = Permission::whereNotIn('name',['staff.index-page','staff.dashboard-page','staff.profile-page','staff.update-profile','student.update-password','staff.logout','staff.login-page','staff.login-check'])->get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$role_id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();
        $data['title'] = 'Permission Update';
        $data['rolePermissions'] = $rolePermissions;
        return view('admin.permissions.permission_update', $data);
    }

    public function update(Request $request, $role_id)
    {
        $this->validate($request, [
            'category_name' => 'required',
            "permission"    => "required|array",
            "permission.*"  => "required",
        ]);
    
        try 
        {
            $staff_category = StaffCategory::where('role_id',$role_id)->update(['category_name' => $request->input('category_name')]);

            $role = Role::find($role_id);
            $role->name = $request->input('category_name');
            $role->save();
            $role->syncPermissions($request->input('permission')); 
            return redirect()->route('admin.permissions.index-page')->with('success','Role updated');
        } catch (Exception $e) {
            return redirect()->route('admin.permissions.index-page')->with('error','Oops something went wrong');
        }
    }
}
