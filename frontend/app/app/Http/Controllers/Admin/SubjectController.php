<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Subject;
use App\Models\SujectOptional;
use App\Models\SubjectClass;
use App\Models\StudentDetail;
use DataTables;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $where['subject_study_level'] = $request->subject_study_level;
            // $where['not_subsidiary'] = $request->not_subsidiary;
            $where['subject_curriculam'] = $request->subject_curriculam;
            $where['is_deleted'] = 'no';
            if($request->subject_curriculam == 'new_curriculam'){
                $subject_core_yes = 'Compulsary';
                $subject_core_no = 'Elective';
            }else{
                $subject_core_yes = 'Yes';
                $subject_core_no = 'No';
            }
            $data = Subject::where($where)->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->editColumn('subject_core', function($data) use($subject_core_yes, $subject_core_no){ 
                    if($data->subject_core == 'yes'){
                        return $subject_core_yes;
                    }else{
                        return $subject_core_no;
                    }
                })
                ->editColumn('subject_study_level', function($data){ 
                    return ucwords(str_replace('_', " ", $data->subject_study_level));
                })
                ->editColumn('not_subsidiary', function($data){ 
                    return ucwords(str_replace('_', " ", $data->not_subsidiary));
                })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->subject_staus == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Subject';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Subject';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.subjects-edit-page',['subject_id' => $row->id]).'"  class="dropdown-item">Edit Subject</a> <a href="'.@route('admin.subjects-delete',['subject_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Subject</a> <a href="'.@route('admin.subjects-status',['subject_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Subject List';
        return view('admin.advanced-features.subjects.index', $data);
    }

    public function create(Request $request)
    {
        if($request->ajax()){
            
            if($request->subject_curriculam == 'new_curriculam'){
                $subject_study_level = 'ordinary_level';
                $not_subsidiary = 'yes';
            }else{
                $subject_study_level = $request->subject_study_level;
                if($subject_study_level == 'ordinary_level'){
                    $not_subsidiary = 'yes';
                }else{
                    $not_subsidiary = $request->not_subsidiary;
                }
            }

            $where['subject_curriculam'] = $request->subject_curriculam;
            $where['subject_study_level'] = $subject_study_level;
            $where['not_subsidiary'] = $not_subsidiary;

            if(!is_null($request->subject_name) && !empty($request->subject_name)){
                $where['subject_name'] = $request->subject_name;
                if(Subject::where($where)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }

            if(!is_null($request->subject_code) && !empty($request->subject_code)){
                $where['subject_code'] = $request->subject_code;
                if(Subject::where($where)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }
        $data['title'] = 'Create Subject';
        return view('admin.advanced-features.subjects.create', $data);
    }

    public function store(Request $request)
    {
        if($request->subject_curriculam == 'new_curriculam'){
            $request['subject_study_level'] = 'ordinary_level';
            $request['not_subsidiary'] = 'yes';
        }
        $request->validate([
            'subject_curriculam' => 'required',
            'subject_name' => [
                                'required',
                                Rule::unique('subjects')
                                ->where(function($query) use($request){
                                    $query->where('subject_curriculam', '=', $request->subject_curriculam);
                                    $query->where('subject_study_level', '=', $request->subject_study_level);
                                    $query->where('not_subsidiary', '=', $request->not_subsidiary);
                                    $query->where('subject_name', $request->subject_name);
                                })
                              ],
            'subject_code' => [
                                'required',
                                Rule::unique('subjects')
                                ->where(function($query) use($request){
                                    $query->where('subject_curriculam', '=', $request->subject_curriculam);
                                    $query->where('subject_study_level', '=', $request->subject_study_level);
                                    $query->where('not_subsidiary', '=', $request->not_subsidiary);
                                    $query->where('subject_code', $request->subject_code);
                                })
                              ],
            'subject_core' => 'required',
            'subject_study_level' => 'required_if:subject_curriculam,ordinary_level',
            'not_subsidiary' => 'required_if:subject_curriculam,yes',
            'not_subsidiary' => 'required_if:subject_study_level,yes',
            'subject_compulsory_papers' => 'required_if:subject_curriculam,0'
        ]);

        try 
        {
            $ins['subject_name'] = $request->subject_name;    
            $ins['subject_code'] = $request->subject_code;    
            $ins['subject_core'] = $request->subject_core;
            $ins['subject_curriculam'] = $request->subject_curriculam;
            $ins['subject_study_level'] = $request->subject_study_level;
            $ins['not_subsidiary'] = $request->not_subsidiary;
            $ins['subject_compulsory_papers'] = $request->subject_compulsory_papers;
            $ins['subject_staus'] = 'active';
            $ins['is_deleted'] = 'no';
            $ins['created_by'] = Auth::user()->id;
            $ins['updated_by'] = Auth::user()->id;
            $subject = Subject::create($ins);
            return redirect()->route('admin.subjects-index-page')->with('success', 'Subject created successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.subjects-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function edit(Request $request, $subject_id)
    {
        if($request->ajax()){
            
            if($request->subject_curriculam == 'new_curriculam'){
                $subject_study_level = 'ordinary_level';
                $not_subsidiary = 'yes';
            }else{
                $subject_study_level = $request->subject_study_level;
                if($subject_study_level == 'ordinary_level'){
                    $not_subsidiary = 'yes';
                }else{
                    $not_subsidiary = $request->not_subsidiary;
                }
            }

            $where['subject_curriculam'] = $request->subject_curriculam;
            $where['subject_study_level'] = $subject_study_level;
            $where['not_subsidiary'] = $not_subsidiary;

            if(!is_null($request->subject_name) && !empty($request->subject_name)){
                $where['subject_name'] = $request->subject_name;
                if(Subject::where($where)->where('id', '!=', $subject_id)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }

            if(!is_null($request->subject_code) && !empty($request->subject_code)){
                $where['subject_code'] = $request->subject_code;
                if(Subject::where($where)->where('id', '!=', $subject_id)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }
        $data['subject'] = Subject::where('id', $subject_id)->first();
        if(is_null($data['subject'])){
            return redirect()->route('admin.subjects-index-page')->with('error', 'Subject not found');
        }
        $data['title'] = 'Edit Subject';
        return view('admin.advanced-features.subjects.edit', $data);
    }

    public function update(Request $request, $subject_id)
    {
        if($request->subject_curriculam == 'new_curriculam'){
            $request['subject_study_level'] = 'ordinary_level';
        }
        $request->validate([
            'subject_curriculam' => 'required',
            'subject_name' => [
                                'required',
                                Rule::unique('subjects')
                                ->where(function($query) use($request, $subject_id){
                                    $query->where('id', '!=', $subject_id);
                                    $query->where('subject_curriculam', '=', $request->subject_curriculam);
                                    $query->where('subject_study_level', '=', $request->subject_study_level);
                                    $query->where('not_subsidiary', '=', $request->not_subsidiary);
                                    $query->where('subject_name', $request->subject_name);
                                })
                              ],
            'subject_code' => [
                                'required',
                                Rule::unique('subjects')
                                ->where(function($query) use($request, $subject_id){
                                    $query->where('id', '!=', $subject_id);
                                    $query->where('subject_curriculam', '=', $request->subject_curriculam);
                                    $query->where('subject_study_level', '=', $request->subject_study_level);
                                    $query->where('not_subsidiary', '=', $request->not_subsidiary);
                                    $query->where('subject_code', $request->subject_code);
                                })
                              ],
            'subject_core' => 'required',
            'subject_study_level' => 'required_if:subject_curriculam,ordinary_level',
            'not_subsidiary' => 'required_if:subject_study_level,yes',
            'subject_compulsory_papers' => 'required_if:subject_curriculam,ordinary_level'
        ]);

        try 
        {
            $upd['subject_name'] = $request->subject_name;    
            $upd['subject_code'] = $request->subject_code;    
            $upd['subject_core'] = $request->subject_core;
            $upd['subject_curriculam'] = $request->subject_curriculam;
            $upd['subject_study_level'] = $request->subject_study_level;
            $upd['not_subsidiary'] = $request->not_subsidiary;
            $upd['subject_compulsory_papers'] = $request->subject_compulsory_papers;
            $upd['updated_by'] = Auth::user()->id;
            $subject = Subject::where('id', $subject_id)->update($upd);
            return redirect()->route('admin.subjects-index-page')->with('success', 'Subject updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.subjects-edit-page',['subject_id' => $subject_id])->with('error', 'Oops something went wrong');
        }
    }

    public function delete(Request $request, $subject_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            Subject::where('id',$subject_id)->update($update);
            return redirect()->route('admin.subjects-index-page')->with('success', 'Subject removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.subjects-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $subject_id, $status){
        $update['subject_staus'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            Subject::where('id',$subject_id)->update($update);
            return redirect()->route('admin.subjects-index-page')->with('success', 'Subject status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.subjects-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function subjectOptional(Request $request)
    {
        if(count($_GET) > 0){
            $where_stud['is_deleted'] = 'no';
            $where_stud['student_staus'] = 'active';
            $where_stud['class_id'] = $request->class_id;
            $data['student_lists'] = StudentDetail::where($where_stud)->get();
            $whr_clsd['id'] = $request->class_id;
            $data['class_detail'] = SubjectClass::where($whr_clsd)->first();
            $whr_sub['is_deleted'] = 'no';
            $whr_sub['subject_staus'] = 'active';
            $data['subject_lists'] = Subject::where($whr_clsd)->get();
        }
        $student_optionals = array();
        $subject_optionals = SujectOptional::where('is_deleted','no')->where('optional_status','active')->get();
        if($subject_optionals->count() > 0){
            foreach ($subject_optionals as $value) {
                $student_optionals[$value->student_id] = [
                    'subject_one' => $value->subject_one,
                    'subject_two' => $value->subject_two,
                    'subject_three' => $value->subject_three,
                ];
            }
        }
        $data['student_optionals'] = $student_optionals;
        $data['title'] = 'Subject Optionals';
        $data['class_lists']  = SubjectClass::where('is_deleted','no')->where('class_level','ordinary_level')->get();
        return view('admin.advanced-features.subjects.subject-optionals', $data);
    }

    public function updateSubjectOptional(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required',
            'subject' => 'required',
            'class_id' => 'required',
            'year' => 'required',
            'student_id' => 'required',
        ]);
   
        if($validator->fails()){
            $response = [
                'success' => false,
                'status'  => 'ERROR',
                'errors'  => $validator->errors(),
                'message' =>  $validator->errors()->all(),
            ];
            return response()->json($response, 200);      
        }

        try 
        {
            $updateComment = SujectOptional::updateOrcreate([
                'class_id' => $request->class_id,
                'term_id' => $request->term_id,
                'year' => $request->year,
                'student_id' => $request->student_id,
            ],[
                'class_id' => $request->class_id,
                'term_id' => $request->term_id,
                'year' => $request->year,
                'student_id' => $request->student_id,
                $request->subject => $request->subject_id,
                'optional_status' => 'active',
                'is_deleted' => 'no',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,                                                               
            ]);

            $response = [
                'success' => true,
                'status'  => 'SUCCESS',
                'errors'  => null,
                'message' => 'Subject updated',
            ];
            return response()->json($response, 200);      
                
        } catch (Exception $e) {
             $response = [
                'success' => false,
                'status'  => 'ERROR',
                'errors'  => $e,
                'message' => 'Oops something went wrong',
            ];
            return response()->json($response, 200);      
        }
    }
}
