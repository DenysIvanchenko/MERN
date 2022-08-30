<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassSetMark;
use App\Models\SubjectClass;
use App\Models\ExamSet;
use DataTables;
use Carbon\Carbon;
use Auth;
use Illuminate\Validation\Rule;
use Validator;

class ExamSetController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ExamSet::where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->set_status == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Exam Set';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Exam Set';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.exam-sets-edit-page',['exam_set_id' => $row->id]).'"  class="dropdown-item">Edit Exam Set</a> 
                                        <a href="'.@route('admin.exam-sets-delete',['exam_set_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Exam Set</a> 
                                        <a href="'.@route('admin.exam-sets-status',['exam_set_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Exam Set List';
        return view('admin.exams.sets.index', $data);
    }

    public function create(Request $request)
    {
        $data['title'] = 'Create Exam Set';
        return view('admin.exams.sets.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'set_curriculam' => 'required',
            'set_name' => [
                             'required',
                             Rule::unique('exam_sets')
                                ->where(function($query) use($request){
                                    $query->where('set_curriculam', '=', $request->set_curriculam);
                                    $query->where('set_name', $request->set_name);
                                })
                          ],
            'set_short_name' => 'required',
            'set_description' => 'required',
        ]);

        try 
        {
            $ins['set_curriculam'] = $request->set_curriculam;
            $ins['set_name'] = $request->set_name;
            $ins['set_short_name'] = $request->set_short_name;
            $ins['set_description'] = $request->set_description;
            $ins['set_status'] = 'active';
            $ins['is_deleted'] = 'no';
            $ins['created_by'] = Auth::user()->id;
            $ins['updated_by'] = Auth::user()->id;
            
            $exam_set = ExamSet::create($ins);
            $class_list = SubjectClass::where('is_deleted', 'no')->get();

            if($class_list->count() > 0){
                foreach ($class_list as $value) {
                    ClassSetMark::updateOrCreate(
                        ['class_id' => $value->id, 'set_id' => $exam_set->id],
                        [
                          'class_id' => $value->id, 
                          'set_id' => $exam_set->id, 
                          'mark' => '100',
                          'mark_set_status' => 'active',
                          'is_deleted' => 'no',
                          'created_by' => Auth::user()->id,
                          'updated_by' => Auth::user()->id,
                        ]);
                }
            }
            return redirect()->route('admin.exam-sets-index-page')->with('success', 'Exam set created successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.exam-sets-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function edit(Request $request, $exam_set_id)
    {
        $data['exam_set'] = ExamSet::where('id', $exam_set_id)->first();
        if(is_null($data['exam_set'])){
            return redirect()->route('admin.exam-sets-index-page')->with('error', 'Exam set not found');
        }
        $data['title'] = 'Edit Exam Set';
        return view('admin.exams.sets.edit', $data);
    }

    public function update(Request $request, $exam_set_id)
    {
        $request->validate([
            'set_name' => [
                             'required',
                             Rule::unique('exam_sets')
                                ->where(function($query) use($request,$exam_set_id){
                                    $query->where('id', '!=', $exam_set_id);
                                    $query->where('set_curriculam', '=', $request->set_curriculam);
                                    $query->where('set_name', $request->set_name);
                                })
                          ],
            'set_short_name' => 'required',
            'set_description' => 'required',
        ]);

        try 
        {
            $upd['set_name'] = $request->set_name;
            $upd['set_short_name'] = $request->set_short_name;
            $upd['set_description'] = $request->set_description;
            $upd['updated_by'] = Auth::user()->id;
            $exam_set = ExamSet::where('id', $exam_set_id)->update($upd);
            return redirect()->route('admin.exam-sets-index-page')->with('success', 'Exam set updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.exam-sets-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function setGradings(Request $request)
    {
        $data['exam_sets']  = ExamSet::where('is_deleted','no')->where('set_status','active')->get();
        $data['class_lists']  = SubjectClass::with('exam_sets')->where('is_deleted','no')->get();
        $data['title'] = 'Set Gradings';
        return view('admin.exams.sets.set_gradings', $data);
    }

    public function setMarkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uniqueRowId' => 'required',
            'uniqueClassId' => 'required',
            'uniqueSetId' => 'required',
            'uniqueValue' => 'required',
        ],['uniqueValue.required' => 'Please enter a valid mark']);
   
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
            $whrClsMrk['class_id'] = $request->uniqueClassId;
            $whrClsMrk['id'] = $request->uniqueRowId;
            $whrClsMrk['set_id'] = $request->uniqueSetId;
            
            $updateClassMark = ClassSetMark::where($whrClsMrk)->update([
                'mark' => $request->uniqueValue,
                'updated_by' => Auth::user()->id,
            ]);

             $response = [
                'success' => true,
                'status'  => 'SUCCESS',
                'errors'  => null,
                'message' => 'Grade updated',
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

    public function marksConsideration(Request $request)
    {
        $data['exam_sets']  = ExamSet::where('is_deleted','no')->get();
        $data['class_lists']  = SubjectClass::with('exam_sets')->where('is_deleted','no')->get();
        $data['title'] = 'Set Gradings';
        return view('admin.exams.sets.marks_consideration', $data);
    }

    public function setmarksConsideration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uniqueRowId' => 'required',
            'uniqueClassId' => 'required',
            'uniqueSetId' => 'required',
            'uniqueStatus' => 'required',
        ],['uniqueStatus.required' => 'Please select the status']);
   
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
            $whrClsMrk['class_id'] = $request->uniqueClassId;
            $whrClsMrk['id'] = $request->uniqueRowId;
            $whrClsMrk['set_id'] = $request->uniqueSetId;
            
            $updateClassMark = ClassSetMark::where($whrClsMrk)->update([
                'mark_set_status' => $request->uniqueStatus,
                'updated_by' => Auth::user()->id,
            ]);

            $response = [
                'success' => true,
                'status'  => 'SUCCESS',
                'errors'  => null,
                'message' => 'Grade updated',
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
    
    public function delete(Request $request, $competency_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            ExamSet::where('id',$competency_id)->update($update);
            return redirect()->route('admin.exam-sets-index-page')->with('success', 'Exam set removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.exam-sets-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $competency_id, $status){
        $update['set_status'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            ExamSet::where('id',$competency_id)->update($update);
            return redirect()->route('admin.exam-sets-index-page')->with('success', 'Set status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.exam-sets-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
