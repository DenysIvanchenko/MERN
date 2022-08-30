<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffCategory;
use Auth;
use DataTables;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class StaffCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StaffCategory::where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->editColumn('staff_type', function($data){ return ucwords(str_replace('_', ' ', $data->staff_type)); })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->category_staus == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Category';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Category';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.staff-categories-edit-page',['category_id' => $row->id]).'"  class="dropdown-item">Edit Category</a> <a href="'.@route('admin.staff-categories-delete',['category_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Category</a> <a href="'.@route('admin.staff-categories-status',['category_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Class List';
        return view('admin.advanced-features.staff-categories.index', $data);
    }

    public function create(Request $request)
    {
        if($request->ajax()){
            if(!is_null($request->category_name) && !empty($request->category_name)){
                if(StaffCategory::where('category_name', $request->category_name)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }
        $data['title'] = 'Create Staff Category';
        return view('admin.advanced-features.staff-categories.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required|unique:staff_categories,category_name',
            'category_description' => 'required',
            'staff_type' => 'required',
            'staff_section'=> 'required_if:staff_type,non_teaching_staff'
        ]);

        $role = Role::updateOrcreate(['name' => $request->input('category_name')]);

        $ins['category_name'] = $request->category_name;
        $ins['category_description'] = $request->category_description;
        $ins['staff_type'] = $request->staff_type;
        $ins['staff_section'] = $request->staff_section;
        $ins['role_id'] = $role->id;
        $ins['category_staus'] = 'active';
        $ins['is_deleted'] = 'no';
        $ins['created_by'] = Auth::user()->id;
        $ins['updated_by'] = Auth::user()->id;

        try 
        {
            StaffCategory::create($ins);
            return redirect()->route('admin.staff-categories-index-page')->with('success', 'Category added successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.staff-categories-index-page')->with('error', 'Oops something wenr wrong');
        }
    }

    public function edit(Request $request, $category_id)
    {
        if($request->ajax()){
            if(!is_null($request->category_name) && !empty($request->category_name)){
                if(StaffCategory::where('category_name', $request->category_name)->where('id','!=',$category_id)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }
        $data['category'] = StaffCategory::where('id', $category_id)->first();
        if(is_null($data['category'])){
            return redirect()->route('admin.staff-categories-index-page')->with('error', 'Category not found');
        }
        $data['title'] = 'Create Staff Category';
        return view('admin.advanced-features.staff-categories.edit', $data);
    }

    public function update(Request $request, $category_id)
    {
        $request->validate([
            'category_name' => 'required|unique:staff_categories,category_name,'.$category_id,
            'category_description' => 'required',
            'staff_type' => 'required',
            'staff_section'=> 'required_if:staff_type,non_teaching_staff'
        ]);

        $role = Role::updateOrcreate(['name' => $request->input('category_name')]);

        $upd['category_name'] = $request->category_name;
        $upd['category_description'] = $request->category_description;
        $upd['staff_type'] = $request->staff_type;
        $upd['staff_section'] = $request->staff_section;
        $upd['role_id'] = $role->id;
        $upd['updated_by'] = Auth::user()->id;

        try 
        {
            StaffCategory::where('id', $category_id)->update($upd);
            return redirect()->route('admin.staff-categories-index-page')->with('success', 'Category updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.staff-categories-index-page')->with('error', 'Oops something wenr wrong');
        }
    }

    public function delete(Request $request, $class_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            StaffCategory::where('id',$class_id)->update($update);
            return redirect()->route('admin.staff-categories-index-page')->with('success', 'Category removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.staff-categories-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $class_id, $status){
        $update['category_staus'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            StaffCategory::where('id',$class_id)->update($update);
            return redirect()->route('admin.staff-categories-index-page')->with('success', 'Category status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.staff-categories-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
