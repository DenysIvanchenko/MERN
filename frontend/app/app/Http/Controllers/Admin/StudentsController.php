<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nationality;
use App\Models\District;
use App\Models\SubjectClass;
use App\Models\Stream;
use App\Models\StudentParent;
use App\Models\StudentDetail;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Hash;
use Auth;
use DataTables;
use Carbon\Carbon;

class StudentsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if($request->class_id && !is_null($request->class_id)){
                $where['class_id'] = $request->class_id;
            }
            if($request->stram_id && !is_null($request->stram_id)){
                $where['stram_id'] = $request->stram_id;
            }
            if($request->year_of_study && !is_null($request->year_of_study)){
                $where['year_of_study'] = $request->year_of_study;
            }
            if($request->term && !is_null($request->term)){
                $where['term'] = $request->term;
            }
            $where['is_deleted'] = 'no';
            $data = StudentDetail::with(['class','parents','stream'])->where($where)->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->editColumn('entry_status', function($data){ return !is_null($data->entry_status) && !empty($data->entry_status) ? $data->entry_status : 'NA'; })
                ->editColumn('district', function($data){ return !is_null($data->district) && !empty($data->district) ? $data->district : 'NA'; })
                ->addColumn('parent', function($data){ 
                    if($data->parent_father_name == null){
                        return 'NA';
                    }else{
                        return $data->parent_father_name;
                    }
                })
                ->addColumn('age', function($data){ 
                    $age = (date('Y') - date('Y',strtotime($data->date_of_birth)));
                    return $age;
                })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->student_staus == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Student';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Student';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.students-edit-page',['student_id' => $row->id]).'"  class="dropdown-item">Edit Student</a> 
                                        <a href="'.@route('admin.students-profile-page',['student_id' => $row->id]).'"  class="dropdown-item">View Student Profile</a> 
                                        <a href="'.@route('admin.students-delete',['student_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Student</a> 
                                        <a href="'.@route('admin.students-status',['student_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = null;
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->get();
        return view('admin.students.index', $data);
    }

    public function trashedStudents(Request $request)
    {
        if ($request->ajax()) {
            if($request->class_id && !is_null($request->class_id)){
                $where['class_id'] = $request->class_id;
            }
            if($request->stram_id && !is_null($request->stram_id)){
                $where['stram_id'] = $request->stram_id;
            }
            if($request->year_of_study && !is_null($request->year_of_study)){
                $where['year_of_study'] = $request->year_of_study;
            }
            if($request->term && !is_null($request->term)){
                $where['term'] = $request->term;
            }
            $where['is_deleted'] = 'yes';
            $data = StudentDetail::with(['class','parents'])->where($where)->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->editColumn('entry_status', function($data){ return !is_null($data->entry_status) && !empty($data->entry_status) ? $data->entry_status : 'NA'; })
                ->editColumn('district', function($data){ return !is_null($data->district) && !empty($data->district) ? $data->district : 'NA'; })
                ->addColumn('parent', function($data){ 
                    if($data->parents == null){
                        return 'NA';
                    }else{
                        return $data->parents->parent_name;
                    }
                })
                ->addColumn('age', function($data){ 
                    $age = (date('Y') - date('Y',strtotime($data->date_of_birth)));
                    return $age;
                })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $actionBtn = '<a href="'.@route('admin.students-restore',['student_id' => $row->id]).'"  class="btn btn-success btn-sm">Restore Student</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Trashed Students';
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->get();
        return view('admin.students.trashed', $data);
    }

    public function studentsSummary(Request $request)
    {
        if ($request->ajax()) {
            if($request->year_of_study && !is_null($request->year_of_study)){
                $whereStu['year_of_study'] = $request->year_of_study;
            }
            if($request->term && !is_null($request->term)){
                $whereStu['term'] = $request->term;
            }
            $whereStu['is_deleted'] = 'no';
            $whereStu['student_staus'] = 'active';
            $where['is_deleted'] = 'no';
            $data = SubjectClass::where($where)->latest()->get();
            return Datatables::of($data)
                ->addColumn('males', function($data,StudentDetail $student_detail) use($whereStu){ 
                    $whr_male['gender'] = 'male';
                    $whr_male['class_id'] = $data->id;
                    return $student_detail->where($whereStu)->where($whr_male)->count();
                })
                ->addColumn('females', function($data,StudentDetail $student_detail) use($whereStu){ 
                    $whr_female['gender'] = 'female';
                    $whr_female['class_id'] = $data->id;
                    return $student_detail->where($whereStu)->where($whr_female)->count();
                })
                ->addColumn('boarding', function($data,StudentDetail $student_detail) use($whereStu){$whr_boarding['residential_status'] = 'boarding';
                    $whr_boarding['class_id'] = $data->id;
                    return $student_detail->where($whereStu)->where($whr_boarding)->count();
                })
                ->addColumn('day', function($data,StudentDetail $student_detail) use($whereStu){
                    $whr_day['residential_status'] = 'day';
                    $whr_day['class_id'] = $data->id;
                    return $student_detail->where($whereStu)->where($whr_day)->count();
                })
                ->addColumn('total', function($data,StudentDetail $student_detail) use($whereStu){
                    $whr_male['residential_status'] = 'day';
                    $whr_male['class_id'] = $data->id;
                    $male_count = $student_detail->where($whereStu)->where($whr_male)->count();
                    
                    $whr_female['gender'] = 'female';
                    $whr_female['class_id'] = $data->id;
                    $female_count = $student_detail->where($whereStu)->where($whr_female)->count();

                    $whr_boarding['residential_status'] = 'boarding';
                    $whr_boarding['class_id'] = $data->id;
                    $boarding_count = $student_detail->where($whereStu)->where($whr_boarding)->count();

                    $whr_day['residential_status'] = 'day';
                    $whr_day['class_id'] = $data->id;
                    $day_count = $student_detail->where($whereStu)->where($whr_day)->count();

                    return ( $boarding_count + $day_count );
                })
                ->addIndexColumn()
                ->make(true);
        }
        $data['title'] = 'Students Summary';
        return view('admin.students.studentsSummary', $data);
    }

    public function create()
    {
        $data['title'] = 'Create Student';
        $data['nationalities'] = Nationality::where('is_deleted', 'no')->get();
        $data['districts'] = District::where('is_deleted', 'no')->get();
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->get();
        $data['parents'] = StudentParent::where('is_deleted', 'no')->get();
        return view('admin.students.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'stram_id' => 'required',
            'entry_status' => 'required',
            'student_name' => [
                                'required',
                                 Rule::unique('student_details')
                                    ->where(function($query) use($request){
                                        $query->where('student_name', $request->student_name);
                                        $query->where('class_id', $request->class_id);
                                    })
                              ],
            'date_of_birth' => 'required',
            'gender' => 'required',
            'address' => 'required',
            'parent_father_name' => 'required',
            'parent_father_telephone' => 'required',
            'parent_father_alive' => 'required',
            'parent_mother_name' => 'required',
            'parent_mother_alive' => 'required',
        ]);

        try 
        {
            $latestStudent = User::orderBy('id','DESC');
            $prefix = 'STMARYS'.$request->year_of_study.''.$request->class_id;

            if($latestStudent->count() > 0){
                $registraion_no = $prefix.''.str_pad($latestStudent->first()->id + 1, 3, "0", STR_PAD_LEFT);    
            }else{
                $registraion_no = $prefix.''.str_pad(0 + 1, 3, "0", STR_PAD_LEFT);
            }
            
            $usr['name'] = $request->student_name;
            $usr['username'] = $registraion_no;
            $usr['password'] = Hash::make($registraion_no);
            $usr['user_type'] = 'student';
            $usr['user_staus'] = 'active';
            $usr['is_deleted'] = 'no';
            $usr['role'] = 2;
            $usr['created_by'] = Auth::user()->id;
            $usr['updated_by'] = Auth::user()->id;

            $user = User::updateOrCreate(['username' => $registraion_no], $usr);
            $user->assignRole([2]);

            $parent['parent_name'] = $request->parent_name;
            $parent['parent_phone_one'] = $request->parent_phone_one;
            $parent['parent_phone_two'] = $request->parent_phone_two;
            $parent['parent_email'] = $request->parent_email;
            $parent['parent_gender'] = $request->parent_gender;
            $parent['parent_address'] = $request->parent_address;
            $parent['parent_staus'] = 'active';
            $parent['is_deleted'] = 'no';
            $parent['created_by'] = Auth::user()->id;
            $parent['updated_by'] = Auth::user()->id;

            $parents = StudentParent::updateOrCreate(['parent_phone_one' => $request->parent_phone_one], $parent);

            $stud['user_id'] = $user->id;
            $stud['student_name'] = $request->student_name;
            $stud['registration_no'] = $registraion_no;
            $stud['date_of_birth'] = $request->date_of_birth;
            $stud['gender'] = $request->gender;
            $stud['nationality'] = $request->nationality;
            $stud['district'] = $request->district;
            $stud['address'] = $request->address;
            $stud['entry_date'] = $request->entry_date;
            $stud['profile_picture'] = $request->user_image;
            $stud['parent_id'] = $parents->id;
            $stud['class_id'] = $request->class_id;
            $stud['stram_id'] = $request->stram_id;
            $stud['term'] = $request->term;
            $stud['year_of_study'] = $request->year_of_study;
            $stud['entry_status'] = $request->entry_status;
            $stud['residential_status'] = $request->residential_status;
            $stud['admission_number'] = $request->admission_number;
            $stud['identification_number'] = $request->identification_number;
            $stud['house_details'] = $request->house_details;
            $stud['former_school'] = $request->former_school;
            $stud['aggregate_obtained_in'] = $request->aggregate_obtained_in;
            $stud['division'] = $request->division;
            $stud['aggregate'] = $request->aggregate;
            $stud['former_school_responsibility'] = $request->former_school_responsibility;
            $stud['having_health_problem'] = $request->having_health_problem;
            $stud['health_problem'] = $request->health_problem;
            $stud['is_baptised_catholic'] = $request->is_baptised_catholic;
            $stud['is_first_holy_communion'] = $request->is_first_holy_communion;
            $stud['received_confirmation'] = $request->received_confirmation;
            $stud['parish_come_from'] = $request->parish_come_from;
            $stud['discese_parish'] = $request->discese_parish;
            $stud['parish_priest_name'] = $request->parish_priest_name;
            $stud['parish_priest_contact'] = $request->parish_priest_contact;
            $stud['a_level_subjects'] = isset($request->a_level_subjects) && !is_null($request->a_level_subjects) ? json_encode($request->a_level_subjects) : null;
            $stud['languages_known'] = $request->languages_known;
            $stud['hobbies'] = $request->hobbies;
            $stud['religion'] = $request->religion;
            $stud['parent_father_name'] = $request->parent_father_name;
            $stud['parent_father_alive'] = $request->parent_father_alive;
            $stud['parent_father_occupation'] = $request->parent_father_occupation;
            $stud['parent_father_email'] = $request->parent_father_email;
            $stud['parent_father_residence'] = $request->parent_father_residence;
            $stud['parent_father_telephone'] = $request->parent_father_telephone;
            $stud['parent_mother_name'] = $request->parent_mother_name;
            $stud['parent_mother_alive'] = $request->parent_mother_alive;
            $stud['parent_mother_occupation'] = $request->parent_mother_occupation;
            $stud['parent_mother_residence'] = $request->parent_mother_residence;
            $stud['parent_telephone'] = $request->parent_telephone;
            $stud['parent_email'] = $request->parent_email;
            $stud['guardian_name'] = $request->guardian_name;
            $stud['guardian_telephone'] = $request->guardian_telephone;
            $stud['who_will_pay_your_fee'] = $request->who_will_pay_your_fee;
            if($request->has('parent_documents'))
            {
                 $file = $request->file('parent_documents');
                 $filename = $file->getClientOriginalName();
                 $file->move(public_path("uploads/parent_documents/"), $filename);
                 $path = 'uploads/parent_documents/' . $filename;
                 $stud['parent_documents'] = $path;
            }
            $stud['student_staus'] = 'active';
            $stud['is_deleted'] = 'no';
            $stud['created_by'] = Auth::user()->id;
            $stud['updated_by'] = Auth::user()->id;

            $student = StudentDetail::create($stud);

            return redirect()->route('admin.students-create-page')->with('success','Student created successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.students-create-page')->with('error','Oops something went wrong');
        }
    }

    public function edit(Request $request, $student_id)
    {
        $data['student_details'] = StudentDetail::with('parents')->where('id', $student_id)->first();
        if(is_null($data['student_details'])){
            return redirect()->route('admin.students-index-page')->with('error','Student not found');
        }
        $data['title'] = 'Edit Student';
        $data['nationalities'] = Nationality::where('is_deleted', 'no')->get();
        $data['districts'] = District::where('is_deleted', 'no')->get();
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->get();
        $data['parents'] = StudentParent::where('is_deleted', 'no')->get();
        return view('admin.students.edit', $data);
    }

    public function update(Request $request, $student_id)
    {
        $request->validate([
            'class_id' => 'required',
            'stram_id' => 'required',
            'entry_status' => 'required',
            'student_name' => [
                                'required',
                                 Rule::unique('student_details')
                                    ->where(function($query) use($request,$student_id){
                                        $query->where('id', '!=', $student_id);
                                        $query->where('student_name', $request->student_name);
                                        $query->where('class_id', $request->class_id);
                                    })
                              ],
            'date_of_birth' => 'required',
            'gender' => 'required',
            'address' => 'required',
            'parent_father_name' => 'required',
            'parent_father_telephone' => 'required',
            'parent_father_alive' => 'required',
            'parent_mother_name' => 'required',
            'parent_mother_alive' => 'required',
        ]);

        try 
        {
            $parent['parent_name'] = $request->parent_name;
            $parent['parent_phone_one'] = $request->parent_phone_one;
            $parent['parent_phone_two'] = $request->parent_phone_two;
            $parent['parent_email'] = $request->parent_email;
            $parent['parent_gender'] = $request->parent_gender;
            $parent['parent_address'] = $request->parent_address;
            $parent['parent_staus'] = 'active';
            $parent['is_deleted'] = 'no';
            $parent['created_by'] = Auth::user()->id;
            $parent['updated_by'] = Auth::user()->id;

            $parents = StudentParent::updateOrCreate(['parent_phone_one' => $request->parent_phone_one], $parent);

            $stud['student_name'] = $request->student_name;
            $stud['date_of_birth'] = $request->date_of_birth;
            $stud['gender'] = $request->gender;
            $stud['nationality'] = $request->nationality;
            $stud['district'] = $request->district;
            $stud['address'] = $request->address;
            $stud['entry_date'] = $request->entry_date;
            $stud['profile_picture'] = $request->user_image;
            $stud['parent_id'] = $parents->id;
            $stud['class_id'] = $request->class_id;
            $stud['stram_id'] = $request->stram_id;
            $stud['term'] = $request->term;
            $stud['year_of_study'] = $request->year_of_study;
            $stud['entry_status'] = $request->entry_status;
            $stud['residential_status'] = $request->residential_status;
            $stud['admission_number'] = $request->admission_number;
            $stud['identification_number'] = $request->identification_number;
            $stud['house_details'] = $request->house_details;
            $stud['former_school'] = $request->former_school;
            $stud['aggregate_obtained_in'] = $request->aggregate_obtained_in;
            $stud['division'] = $request->division;
            $stud['aggregate'] = $request->aggregate;
            $stud['former_school_responsibility'] = $request->former_school_responsibility;
            $stud['having_health_problem'] = $request->having_health_problem;
            $stud['health_problem'] = $request->health_problem;
            $stud['is_baptised_catholic'] = $request->is_baptised_catholic;
            $stud['is_first_holy_communion'] = $request->is_first_holy_communion;
            $stud['received_confirmation'] = $request->received_confirmation;
            $stud['parish_come_from'] = $request->parish_come_from;
            $stud['discese_parish'] = $request->discese_parish;
            $stud['parish_priest_name'] = $request->parish_priest_name;
            $stud['parish_priest_contact'] = $request->parish_priest_contact;
            $stud['a_level_subjects'] = isset($request->a_level_subjects) && !is_null($request->a_level_subjects) ? json_encode($request->a_level_subjects) : null;
            $stud['languages_known'] = $request->languages_known;
            $stud['hobbies'] = $request->hobbies;
            $stud['religion'] = $request->religion;
            $stud['parent_father_name'] = $request->parent_father_name;
            $stud['parent_father_alive'] = $request->parent_father_alive;
            $stud['parent_father_occupation'] = $request->parent_father_occupation;
            $stud['parent_father_residence'] = $request->parent_father_residence;
            $stud['parent_father_email'] = $request->parent_father_email;
            $stud['parent_father_telephone'] = $request->parent_father_telephone;
            $stud['parent_mother_name'] = $request->parent_mother_name;
            $stud['parent_mother_alive'] = $request->parent_mother_alive;
            $stud['parent_mother_occupation'] = $request->parent_mother_occupation;
            $stud['parent_mother_residence'] = $request->parent_mother_residence;
            $stud['parent_telephone'] = $request->parent_telephone;
            $stud['parent_email'] = $request->parent_email;
            $stud['guardian_name'] = $request->guardian_name;
            $stud['guardian_telephone'] = $request->guardian_telephone;
            $stud['who_will_pay_your_fee'] = $request->who_will_pay_your_fee;
            $stud['updated_by'] = Auth::user()->id;
            if($request->has('parent_documents'))
            {
                 $file = $request->file('parent_documents');
                 $filename = $file->getClientOriginalName();
                 $file->move(public_path("uploads/parent_documents/"), $filename);
                 $path = 'uploads/parent_documents/' . $filename;
                 $stud['parent_documents'] = $path;
            }
            $student = StudentDetail::where('id', $student_id)->update($stud);

            return redirect()->route('admin.students-view-page')->with('success','Student updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.students-edit-page',['student_id' => $student_id])->with('error','Oops something went wrong');
        }
    }

    public function quickRegistration()
    {
        $data['title'] = 'Quick Student Registration';
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->get();
        $data['parents'] = StudentParent::where('is_deleted', 'no')->get();
        $data['nationalities'] = Nationality::where('is_deleted', 'no')->get();
        return view('admin.students.quick-student', $data);
    }

    public function storeQuickRegistration(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'stram_id' => 'required',
            'entry_status' => 'required',
            'student_name' => [
                                'required',
                                 Rule::unique('student_details')
                                    ->where(function($query) use($request){
                                        $query->where('student_name', $request->student_name);
                                        $query->where('class_id', $request->class_id);
                                    })
                              ],
            'date_of_birth' => 'required',
            'gender' => 'required',
            'nationality' => 'required',
            'term' => 'required',
            'year_of_study' => 'required',
            'entry_status' => 'required',
            'residential_status' => 'required',
        ]);

        try 
        {
            $latestStudent = User::orderBy('id','DESC');
            $prefix = 'STMARYS'.$request->year_of_study.''.$request->class_id;

            if($latestStudent->count() > 0){
                $registraion_no = $prefix.''.str_pad($latestStudent->first()->id + 1, 3, "0", STR_PAD_LEFT);    
            }else{
                $registraion_no = $prefix.''.str_pad(0 + 1, 3, "0", STR_PAD_LEFT);
            }
            
            $usr['name'] = $request->student_name;
            $usr['username'] = $registraion_no;
            $usr['password'] = Hash::make($registraion_no);
            $usr['user_type'] = 'student';
            $usr['user_staus'] = 'active';
            $usr['is_deleted'] = 'no';
            $usr['role'] = 2;
            $usr['created_by'] = Auth::user()->id;
            $usr['updated_by'] = Auth::user()->id;

            $user = User::updateOrCreate(['username' => $registraion_no], $usr);
            $user->assignRole([2]);

            $stud['user_id'] = $user->id;
            $stud['student_name'] = $request->student_name;
            $stud['registration_no'] = $registraion_no;
            $stud['date_of_birth'] = $request->date_of_birth;
            $stud['gender'] = $request->gender;
            $stud['nationality'] = $request->nationality;
            $stud['class_id'] = $request->class_id;
            $stud['stram_id'] = $request->stram_id;
            $stud['term'] = $request->term;
            $stud['year_of_study'] = $request->year_of_study;
            $stud['entry_status'] = $request->entry_status;
            $stud['residential_status'] = $request->residential_status;
            $stud['student_staus'] = 'active';
            $stud['is_deleted'] = 'no';
            $stud['created_by'] = Auth::user()->id;
            $stud['updated_by'] = Auth::user()->id;

            $student = StudentDetail::create($stud);

            return redirect()->route('admin.students-quick-student-page')->with('success','Student created successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.students-quick-student-page')->with('error','Oops something went wrong');
        }
    }

    public function getStream(Request $request)
    {
        $streams = Stream::where('class_id', $request->class_id)->get();
        $return = '<option value="">Select</option>';
        if(isset($streams) && $streams->count() > 0){
            foreach ($streams as $key => $value) {
                $return.= '<option value="'.$value->id.'">'.$value->stream_name.'</option>';
            }
        }
        return $return;
    }

    public function studentImport(Request $request)
    {
        $data['title'] = 'Student Import';
        $data['classes'] = SubjectClass::where('is_deleted', 'no')->get();
        return view('admin.students.import', $data);
    }

    public function poststudentImport(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'stram_id' => 'required',
            'term' => 'required',
            'year_of_study' => 'required',
            'residential_status' => 'required',
        ]);

        Excel::import(new StudentsImport(
            $request->class_id,
            $request->term,
            $request->year_of_study,
            $request->stram_id,
            $request->residential_status
        ), $request->file('file')->store('temp'));
        return redirect()->route('admin.students-quick-student-page');
    }

    public function studentsProfile(Request $request, $student_id){
        $data['title'] = 'Student Profile';
        $data['students'] = StudentDetail::with(['parents','stream','class'])->where('id', $student_id)->first();
        if(is_null($data['students'])){
            return redirect()->route('admin.students-view-page')->with('success', 'Student not found');
        }
        return view('admin.students.studentProfile', $data);
    }

    public function getParentDetails(Request $request){
        $data = null;
        $status = "SUCCESS";
        $parents = StudentParent::where('id', $request->parent_id);
        if($parents->count() > 0){
            $data = $parents->first();
        }else{
            $status = "ERROR";
        }
        return response()->json([
            "data" => $data,
            "status" => $status,
        ]);
    }

    public function parentStudents(Request $request)
    {
        if ($request->ajax()) {
            $where['is_deleted'] = 'no';
            $data = StudentParent::with('student_details')->where($where)->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addColumn('students', function($data){ 
                    if($data->student_details == null){
                        return 'NA';
                    }else{
                        $students_list = '';
                        foreach($data->student_details as $key => $stu){
                            $students_list.= ($key+1).'. <a href="#">'.$stu->student_name.'</a><br>';
                        }
                        return $students_list;
                    }
                })
                ->addColumn('age', function($data){ 
                    $age = (date('Y') - date('Y',strtotime($data->date_of_birth)));
                    return $age;
                })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $actionBtn = '<button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.students-parents-edit-page',['parent_id' => $row->id]).'"  class="dropdown-item">Edit Parents</a>  
                                        <a href="#" data-parent_id="'.$row->id.'" class="dropdown-item add_students">Add Students</a>
                                        <a href="'.@route('admin.students-parents-delete',['parent_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Parent</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action','students'])
                ->make(true);
        }
        $data['title'] = 'Student Parents';
        $data['students'] = StudentDetail::where('is_deleted', 'no')->get();
        return view('admin.students.parents', $data);
    }

    public function parentAddStudents(Request $request)
    {
        $request->validate([
            'parent_id' => 'required',
            'student_id' => 'required'
        ]);

        try 
        {
            $update['parent_id'] = $request->parent_id;
            $update['updated_by'] = Auth::user()->id;
            $where['id'] = $request->student_id;
            StudentDetail::where($where)->update($update);
            return redirect()->route('admin.students-parents-page')->with('success', 'Student added successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.students-parents-page')->with('error', 'Oops something went wrong');
        }
    }

    public function parentsEdit(Request $request, $parent_id)
    {
        $data['parents'] = StudentParent::where('id', $parent_id)->first();
        if(is_null($data['parents'])){
            return redirect()->route('admin.students-parents-page')->with('error', 'Parent not found');
        }
        $data['title'] = 'Parent Edit Page';
        return view('admin.students.edit_parent',$data);
    }

    public function parentsUpdate(Request $request, $parent_id)
    {
        $request->validate([
            'parent_name' => 'required',
            'parent_email' => [
                                'required',
                                'email',
                                Rule::unique('parents')
                                ->where(function($query) use($request,$parent_id){
                                    $query->where('id', '!=', $parent_id);
                                    $query->where('parent_email', $request->parent_email);
                                })
                            ],
             'parent_phone_one' => [
                                'required',
                                Rule::unique('parents')
                                ->where(function($query) use($request,$parent_id){
                                    $query->where('id', '!=', $parent_id);
                                    $query->where('parent_phone_one', $request->parent_phone_one);
                                })
                            ],
            'parent_gender' => 'required',
            'parent_address' => 'required',
        ]);

        try 
        {
            $update['parent_name'] = $request->parent_name;
            $update['parent_phone_one'] = $request->parent_phone_one;
            $update['parent_phone_two'] = $request->parent_phone_two;
            $update['parent_email'] = $request->parent_email;
            $update['parent_gender'] = $request->parent_gender;
            $update['parent_address'] = $request->parent_address;
            $update['updated_by'] = Auth::user()->id;
            $where['id'] = $parent_id;
            StudentParent::where($where)->update($update);
            return redirect()->route('admin.students-parents-page')->with('success', 'Parents updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.students-parents-page')->with('error', 'Oops something went wrong');
        }
    }

    public function parentsDelete(Request $request, $parent_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            StudentParent::where('id', $parent_id)->update($update);
            return redirect()->route('admin.students-parents-page')->with('success', 'Parent removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.students-parents-page')->with('error', 'Oops something went wrong');
        }
    }

    public function delete(Request $request, $student_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        $student_detail = StudentDetail::where('id',$student_id);
        try {
            User::where('id', $student_detail->first()->user_id)->update($update);
            $student_detail->update($update);
            return redirect()->route('admin.students-view-page')->with('success', 'Student removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.students-view-page')->with('error', 'Oops something went wrong');
        }
    }

    public function restore(Request $request, $student_id){
        $update['is_deleted'] = 'no';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = null;
        $update['deleted_at'] = null;
        $student_detail = StudentDetail::where('id',$student_id);
        try {
            User::where('id', $student_detail->first()->user_id)->update($update);
            $student_detail->update($update);
            return redirect()->route('admin.students-view-page')->with('success', 'Student restored successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.students-view-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $student_id, $status){
        $update['student_staus'] = $status;
        $update['updated_by'] = Auth::user()->id;
        $student_detail = StudentDetail::where('id',$student_id);
        try {
            User::where('id', $student_detail->first()->user_id)->update(['user_staus' => $status]);
            $student_detail->update($update);
            return redirect()->route('admin.students-view-page')->with('success', 'Student status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.students-view-page')->with('error', 'Oops something went wrong');
        }
    }
}
