<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffDetail;
use App\Models\StaffCategory;
use App\Models\User;
use App\Models\Nationality;
use App\Models\District;
use App\Models\Subject;
use App\Models\SubjectClass;
use Auth;
use DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class StaffsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StaffDetail::with('category')->where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->editColumn('staff_position', function($data){ return ucwords(str_replace('_', " ",$data->staff_position)); })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->staff_staus == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Staff';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Staff';
                    }

                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.staffs-edit-page',['staff_id' => $row->id]).'"  class="dropdown-item">Edit Staff</a>
                                        <a href="'.@route('admin.staffs-enroll-page',['staff_id' => $row->id]).'"  class="dropdown-item">Enroll Staff</a>
                                        <a href="'.@route('admin.staffs-delete',['staff_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Staff</a> 
                                        <a href="'.@route('admin.staffs-status',['staff_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Staffs List';
        return view('admin.advanced-features.staffs.index', $data);
    }

    public function enroll(Request $request, $staff_id)
    {
        $data['staff'] = StaffDetail::where('id', $staff_id)->first();
        if(is_null($data['staff'])){
            return redirect()->route('admin.staffs-index-page')->with('error', 'Staff not found');
        }
        if($request->ajax()){
            if(!is_null($request->username) && !empty($request->username)){
                if(isset($data['staff']->user_id) && !is_null($data['staff']->user_id)){
                    $usr_count = User::where('username', $request->username)->where('id', '!=', $data['staff']->user_id)->count();    
                }else{
                    $usr_count = User::where('username', $request->username)->count();
                }
                if($usr_count > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }
        $data['title'] = 'Staff Enroll Page';
        return view('admin.advanced-features.staffs.enroll', $data);
    }

    public function enrollUpdate(Request $request, $staff_id)
    {
        $staff = StaffDetail::where('id', $staff_id);
        if(isset($staff->first()->user_id) && !is_null($staff->first()->user_id)){
            $user_id = User::where('id', $staff->first()->user_id)->first()->id;  
        }else{
            $user_id = null;
        }
        
        $request->validate([
            'username' => 'required|unique:users,username,'.$user_id,
            'password' => 'required',
        ]);

        try 
        {
            $credentials['name'] = $staff->first()->staff_name;
            $credentials['username'] = $request->username;
            $credentials['password'] = Hash::make($request->password);
            $credentials['email'] = $staff->first()->staff_email;
            $credentials['user_type'] = 'staff';
            $credentials['updated_by'] = Auth::user()->id;
            $user = User::updateOrCreate(['username' => $request->username], $credentials);
            $staff->update(['user_id' => $user->id]);
            $role_id = StaffCategory::where('id', $staff->first()->staff_category_id)->first();
            $user->assignRole([$role_id->role_id]);
            return redirect()->route('admin.staffs-index-page')->with('success', 'Staff credentials updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.staffs-enroll-page',['staff_id' => $staff_id])->with('error', 'Oops something went wrong');
        }
    }

    public function create(Request $request)
    {
        $data['title'] = 'Create Staff';
        $data['nationalities'] = Nationality::where('is_deleted', 'no')->get();
        $data['districts'] = District::where('is_deleted', 'no')->get();
        $data['class_lists'] = SubjectClass::where('class_status','active')->where('is_deleted','no')->get();
        $data['subjects'] = Subject::where('subject_staus','active')->where('is_deleted','no')->get();
        return view('admin.advanced-features.staffs.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_name' => 'required|unique:staff_details,staff_name',
            'staff_gender' => 'required',
            'staff_email' => 'required|unique:staff_details,staff_email',
            'staff_date_of_birth' => 'required',
            'staff_nationality' => 'required',
            'staff_home_address' => 'required',
            'staff_contact_one' => 'required',
            'staff_high_level_education' => 'required',
            'staff_year_of_joining_in_school' => 'required',
            'staff_type' => 'required',
            'staff_category_id' => 'required',
            'staff_contract_type' => 'required',
        ]);

        $ins['staff_name'] = $request->staff_name;
        $ins['staff_gender'] = $request->staff_gender;
        $ins['staff_date_of_birth'] = $request->staff_date_of_birth;
        $ins['staff_marital_status'] = $request->staff_marital_status;
        $ins['staff_religious_affiliation'] = $request->staff_religious_affiliation;
        $ins['staff_next_of_kin'] = $request->staff_next_of_kin;
        $ins['staff_next_of_kin_contact'] = $request->staff_next_of_kin_contact;
        $ins['staff_nationality'] = $request->staff_nationality;
        $ins['staff_home_address'] = $request->staff_home_address;
        $ins['staff_contact_one'] = $request->staff_contact_one;
        $ins['staff_contact_two'] = $request->staff_contact_two;
        $ins['staff_email'] = $request->staff_email;
        $ins['staff_high_level_education'] = $request->staff_high_level_education;
        $ins['staff_year_of_joining_in_school'] = $request->staff_year_of_joining_in_school;
        $ins['staff_type'] = $request->staff_type;
        $ins['staff_category_id'] = $request->staff_category_id;
        $ins['staff_teaching_subjects'] =isset($request->staff_teaching_subjects) && !is_null($request->staff_teaching_subjects) ? json_encode($request->staff_teaching_subjects) : null;
        $ins['staff_initial'] = $request->staff_initial;
        $ins['staff_teaching_experience'] = $request->staff_teaching_experience;
        $ins['staff_position'] = $request->staff_position;
        $ins['staff_contract_type'] = $request->staff_contract_type;
        $ins['number_of_children'] = $request->number_of_children;
        $ins['district'] = $request->district;
        $ins['country'] = $request->country;
        $ins['sub_country'] = $request->sub_country;
        $ins['parish'] = $request->parish;
        $ins['village'] = $request->village;
        $ins['classes_you_teach'] = isset($request->staff_teaching_subjects) && !is_null($request->staff_teaching_subjects) ? json_encode($request->staff_teaching_subjects) : null;;
        $ins['parent_father_name'] = $request->parent_father_name;
        $ins['parent_father_alive'] = $request->parent_father_alive;
        $ins['parent_father_occupation'] = $request->parent_father_occupation;
        $ins['parent_mother_name'] = $request->parent_mother_name;
        $ins['parent_mother_alive'] = $request->parent_mother_alive;
        $ins['parent_mother_occupation'] = $request->parent_mother_occupation;
        $ins['having_health_problem'] = $request->having_health_problem;
        $ins['health_problem'] = $request->health_problem;
        if($request->has('staff_documents'))
        {
             $file = $request->file('staff_documents');
             $filename = $file->getClientOriginalName();
             $file->move(public_path("uploads/staff_documents/"), $filename);
             $path = 'uploads/staff_documents/' . $filename;
             $ins['staff_documents'] = $path;
        }
        $ins['staff_staus'] = 'active';
        $ins['is_deleted'] = 'no';
        $ins['created_by'] = Auth::user()->id;
        $ins['updated_by'] = Auth::user()->id;

        try 
        {
            StaffDetail::create($ins);
            return redirect()->route('admin.staffs-index-page')->with('success', 'Staff created successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.staffs-create-page')->with('error', 'Oops something went wrong');
        }
    }

    public function edit(Request $request, $staff_id)
    {
        $data['staff'] = StaffDetail::where('id', $staff_id)->first();
        if(is_null($data['staff'])){
            return redirect()->route()->with('error', 'Staff not found');
        }
        $data['title'] = 'Edit Staff';
        $data['nationalities'] = Nationality::where('is_deleted', 'no')->get();
        $data['districts'] = District::where('is_deleted', 'no')->get();
        $data['class_lists'] = SubjectClass::where('class_status','active')->where('is_deleted','no')->get();
        $data['subjects'] = Subject::where('subject_staus','active')->where('is_deleted','no')->get();
        return view('admin.advanced-features.staffs.edit', $data);
    }

    public function update(Request $request, $staff_id)
    {
        $request->validate([
            'staff_name' => 'required|unique:staff_details,staff_name,'.$staff_id,
            'staff_gender' => 'required',
            'staff_email' => 'required|unique:staff_details,staff_email,'.$staff_id,
            'staff_date_of_birth' => 'required',
            'staff_nationality' => 'required',
            'staff_home_address' => 'required',
            'staff_contact_one' => 'required',
            'staff_high_level_education' => 'required',
            'staff_year_of_joining_in_school' => 'required',
            'staff_type' => 'required',
            'staff_category_id' => 'required',
            'staff_contract_type' => 'required',
        ]);

        $upd['staff_name'] = $request->staff_name;
        $upd['staff_gender'] = $request->staff_gender;
        $upd['staff_date_of_birth'] = $request->staff_date_of_birth;
        $upd['staff_marital_status'] = $request->staff_marital_status;
        $upd['staff_religious_affiliation'] = $request->staff_religious_affiliation;
        $upd['staff_next_of_kin'] = $request->staff_next_of_kin;
        $upd['staff_next_of_kin_contact'] = $request->staff_next_of_kin_contact;
        $upd['staff_nationality'] = $request->staff_nationality;
        $upd['staff_home_address'] = $request->staff_home_address;
        $upd['staff_contact_one'] = $request->staff_contact_one;
        $upd['staff_contact_two'] = $request->staff_contact_two;
        $upd['staff_email'] = $request->staff_email;
        $upd['staff_high_level_education'] = $request->staff_high_level_education;
        $upd['staff_year_of_joining_in_school'] = $request->staff_year_of_joining_in_school;
        $upd['staff_type'] = $request->staff_type;
        $upd['staff_category_id'] = $request->staff_category_id;
        $upd['staff_teaching_subjects'] = isset($request->staff_teaching_subjects) && !is_null($request->staff_teaching_subjects) ? json_encode($request->staff_teaching_subjects) : null;
        $upd['staff_initial'] = $request->staff_initial;
        $upd['staff_teaching_experience'] = $request->staff_teaching_experience;
        $upd['staff_position'] = $request->staff_position;
        $upd['staff_contract_type'] = $request->staff_contract_type;
        $upd['number_of_children'] = $request->number_of_children;
        $upd['district'] = $request->district;
        $upd['country'] = $request->country;
        $upd['sub_country'] = $request->sub_country;
        $upd['parish'] = $request->parish;
        $upd['village'] = $request->village;
        $upd['classes_you_teach'] = isset($request->classes_you_teach) && !is_null($request->classes_you_teach) ? json_encode($request->classes_you_teach) : null;
        $upd['parent_father_name'] = $request->parent_father_name;
        $upd['parent_father_alive'] = $request->parent_father_alive;
        $upd['parent_father_occupation'] = $request->parent_father_occupation;
        $upd['parent_mother_name'] = $request->parent_mother_name;
        $upd['parent_mother_alive'] = $request->parent_mother_alive;
        $upd['parent_mother_occupation'] = $request->parent_mother_occupation;
        $upd['having_health_problem'] = $request->having_health_problem;
        $upd['health_problem'] = $request->health_problem;
        if($request->has('staff_documents'))
        {
             $file = $request->file('staff_documents');
             $filename = $file->getClientOriginalName();
             $file->move(public_path("uploads/staff_documents/"), $filename);
             $path = 'uploads/staff_documents/' . $filename;
             $upd['staff_documents'] = $path;
        }
        $upd['updated_by'] = Auth::user()->id;

        try 
        {
            StaffDetail::where('id', $staff_id)->update($upd);
            return redirect()->route('admin.staffs-index-page')->with('success', 'Staff updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.staffs-edit-page',['staff_id' => $staff_id])->with('error', 'Oops something went wrong');
        }
    }

    public function getCategory(Request $request)
    {
        $return = '<option value="">Select</option>';
        if($request->type == 'teaching_staff'){
            $categories = StaffCategory::where('staff_type', 'teaching_staff')->get();
            if($categories->count() > 0){
                foreach ($categories as $category) {
                    $return.= '<option value="'.$category->id.'">'.$category->category_name.'</option>';
                }
            }
        }else if($request->type == 'non_teaching_staff'){
            $categories = StaffCategory::where('staff_type', 'non_teaching_staff')->where('staff_section', $request->section)->get();
            if($categories->count() > 0){
                foreach ($categories as $category) {
                    $return.= '<option value="'.$category->id.'">'.$category->category_name.'</option>';
                }
            }
        }
        return $return;
    }

    public function delete(Request $request, $staff_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            StaffDetail::where('id',$staff_id)->update($update);
            return redirect()->route('admin.staffs-index-page')->with('success', 'Staff removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.staffs-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $staff_id, $status){
        $update['staff_staus'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            StaffDetail::where('id',$staff_id)->update($update);
            return redirect()->route('admin.staffs-index-page')->with('success', 'Staff status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.staffs-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
