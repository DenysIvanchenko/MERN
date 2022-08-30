<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DataTables;
use App\Models\NewCurriculamCompetency;
use App\Models\Subject;
use App\Models\SubjectClass;
use App\Models\ExamSet;
use Carbon\Carbon;

class CompetencyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:staff.new-curriculam-competencies-index-page|staff.new-curriculam-competencies-create-page|staff.new-curriculam-competencies-edit-page|staff.new-curriculam-competencies-delete|staff.new-curriculam-competencies-status']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $where['is_deleted'] = 'no';
            $data = NewCurriculamCompetency::with(['class','subject','exam_set'])->where($where)->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->editColumn('term_id', function($data){  return ucwords(str_replace("_"," ",$data->term_id)); })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->competency_status == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Competency';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Competency';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.new-curriculam-competencies-edit-page',['competency_id' => $row->id]).'"  class="dropdown-item">Edit Competency</a> <a href="'.@route('admin.new-curriculam-competencies-delete',['competency_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Competency</a> <a href="'.@route('admin.new-curriculam-competencies-status',['competency_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Competency List';
        return view('admin.new-curriculam.competency.index', $data);
    }

    public function create(Request $request)
    {
        $data['title'] = 'Create Competency';
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_curriculam','new_curriculam')->get();
        $data['subject_lists'] = Subject::where('is_deleted', 'no')->where('subject_staus','active')->where('subject_curriculam','new_curriculam')->get();
        $data['set_lists'] = ExamSet::where('is_deleted', 'no')->where('set_status','active')->where('set_curriculam','new_curriculam')->get();
        return view('admin.new-curriculam.competency.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'subject_id' => 'required',
            'term_id' => 'required',
            'unit_set_id' => 'required',
            'competency_description' => 'required',
        ]);

        try 
        {
            $whr_exis['class_id'] = $request->class_id;
            $whr_exis['subject_id'] = $request->subject_id;
            $whr_exis['term_id'] = $request->term_id;
            $whr_exis['unit_set_id'] = $request->unit_set_id;
            $already_exists = NewCurriculamCompetency::where($whr_exis)->count();
            if($already_exists > 0){
                redirect()->back()
                    ->withInput($request->only('class_id'))
                    ->withErrors([
                        'class_id' => 'Competency already exists',
                    ]);
            }
            $ins['class_id'] = $request->class_id;
            $ins['subject_id'] = $request->subject_id;
            $ins['term_id'] = $request->term_id;
            $ins['unit_set_id'] = $request->unit_set_id;
            $ins['competency_description'] = $request->competency_description;
            $ins['competency_status'] = 'active';
            $ins['is_deleted'] = 'no';
            $ins['created_by'] = Auth::user()->id;
            $ins['updated_by'] = Auth::user()->id;
            $create = NewCurriculamCompetency::create($ins);
            return redirect()->route('admin.new-curriculam-competencies-index-page')->with('success','Competency creared successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.new-curriculam-competencies-index-page')->with('error','Oops something went wrong');
        }
    }

    public function edit(Request $request, $competency_id)
    {
        $data['competency'] = NewCurriculamCompetency::where('id', $competency_id)->first();
        if(is_null($data['competency'])){
            return redirect()->route('admin.new-curriculam-competencies-index-page')->with('error','Competency not found');
        }
        $data['title'] = 'Edit Competency';
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_curriculam','new_curriculam')->get();
        $data['subject_lists'] = Subject::where('is_deleted', 'no')->where('subject_staus','active')->where('subject_curriculam','new_curriculam')->get();
        $data['set_lists'] = ExamSet::where('is_deleted', 'no')->where('set_status','active')->where('set_curriculam','new_curriculam')->get();
        return view('admin.new-curriculam.competency.edit', $data);
    }

    public function update(Request $request, $competency_id)
    {
        $request->validate([
            'class_id' => 'required',
            'subject_id' => 'required',
            'term_id' => 'required',
            'unit_set_id' => 'required',
            'competency_description' => 'required',
        ]);

        try 
        {
            $whr_exis['class_id'] = $request->class_id;
            $whr_exis['subject_id'] = $request->subject_id;
            $whr_exis['term_id'] = $request->term_id;
            $whr_exis['unit_set_id'] = $request->unit_set_id;
            $already_exists = NewCurriculamCompetency::where('id','!=',$competency_id)->where($whr_exis)->count();
            if($already_exists > 0){
                redirect()->back()
                    ->withInput($request->only('class_id'))
                    ->withErrors([
                        'class_id' => 'Competency already exists',
                    ]);
            }
            $upd['class_id'] = $request->class_id;
            $upd['subject_id'] = $request->subject_id;
            $upd['term_id'] = $request->term_id;
            $upd['unit_set_id'] = $request->unit_set_id;
            $upd['competency_description'] = $request->competency_description;
            $create = NewCurriculamCompetency::where('id',$competency_id)->update($upd);
            return redirect()->route('admin.new-curriculam-competencies-index-page')->with('success','Competency updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.new-curriculam-competencies-index-page')->with('error','Oops something went wrong');
        }
    }

    public function delete(Request $request, $competency_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            NewCurriculamCompetency::where('id',$competency_id)->update($update);
            return redirect()->route('admin.new-curriculam-competencies-index-page')->with('success', 'Competency removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.new-curriculam-competencies-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $competency_id, $status){
        $update['competency_status'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            NewCurriculamCompetency::where('id',$competency_id)->update($update);
            return redirect()->route('admin.new-curriculam-competencies-index-page')->with('success', 'Competency status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.new-curriculam-competencies-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
