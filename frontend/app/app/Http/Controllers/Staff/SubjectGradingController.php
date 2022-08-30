<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\SubjectGrading;
use DataTables;
use Carbon\Carbon;

class SubjectGradingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SubjectGrading::where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->editColumn('study_level', function($data){ return ucwords(str_replace("_", " ",$data->study_level)); })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->grade_status == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Grade';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Grade';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('staff.exams-gradings-edit-page',['grade_id' => $row->id]).'"  class="dropdown-item">Edit Grade</a> <a href="'.@route('staff.exams-gradings-delete',['grade_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Grade</a> <a href="'.@route('staff.exams-gradings-status',['grade_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Grade List';
        return view('staff.exams.gradings.index', $data);
    }

    public function create(Request $request)
    {
        $data['title'] = 'Create Grade';
        return view('staff.exams.gradings.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'study_level' => 'required',
            'marks_from' => 'required',
            'marks_to' => 'required',
            'grade' => 'required',
            'comments' => 'required',
        ]);

        try 
        {
            $ins['study_level'] = $request->study_level;    
            $ins['marks_from'] = $request->marks_from;
            $ins['marks_to'] = $request->marks_to;
            $ins['grade'] = $request->grade;
            $ins['comments'] = $request->comments;
            $ins['grade_status'] = 'active';
            $ins['is_deleted'] = 'no';
            $ins['created_by'] = Auth::user()->id;
            $ins['updated_by'] = Auth::user()->id;
            $insert_grade = SubjectGrading::create($ins);
            return redirect()->route('staff.exams-gradings-index-page')->with('success', 'Grading added successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.exams-gradings-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function edit(Request $request, $grade_id)
    {
        $data['grading'] = SubjectGrading::where('id', $grade_id)->first();
        if(is_null($data['grading'])){
            return redirect()->route('staff.exams-gradings-index-page')->with('error', 'Grading not found');
        }
        $data['title'] = 'Create Grade';
        return view('staff.exams.gradings.edit', $data);
    }

    public function update(Request $request, $grade_id)
    {
        $request->validate([
            'study_level' => 'required',
            'marks_from' => 'required',
            'marks_to' => 'required',
            'grade' => 'required',
            'comments' => 'required',
        ]);

        try 
        {
            $upd['study_level'] = $request->study_level;    
            $upd['marks_from'] = $request->marks_from;
            $upd['marks_to'] = $request->marks_to;
            $upd['grade'] = $request->grade;
            $upd['comments'] = $request->comments;
            $upd['updated_by'] = Auth::user()->id;
            $update_grade = SubjectGrading::where('id',$grade_id)->update($upd);
            return redirect()->route('staff.exams-gradings-index-page')->with('success', 'Grading updated successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.exams-gradings-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function delete(Request $request, $grade_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            SubjectGrading::where('id',$grade_id)->update($update);
            return redirect()->route('staff.exams-gradings-index-page')->with('success', 'Grading removed successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.exams-gradings-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $grade_id, $status){
        $update['grade_status'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            SubjectGrading::where('id',$grade_id)->update($update);
            return redirect()->route('staff.exams-gradings-index-page')->with('success', 'Grading status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.exams-gradings-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
