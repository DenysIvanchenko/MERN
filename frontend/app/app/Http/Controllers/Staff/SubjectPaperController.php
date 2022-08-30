<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DataTables;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Models\SubjectPaper;
use App\Models\Subject;

class SubjectPaperController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SubjectPaper::with('subject')->where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('staff.subject-papers-edit-page',['paper_id' => $row->id]).'"  class="dropdown-item">Edit Paper</a> <a href="'.@route('staff.subject-papers-delete',['paper_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Paper</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Papers List';
        return view('staff.advanced-features.subjects.papers.index', $data);
    }

    public function papersAttempted(Request $request)
    {
        if ($request->ajax()) {
            $data = SubjectPaper::with('subject')->where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->paper_staus == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Paper';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Paper';
                    }
                    $actionBtn = '<a href="'.@route('staff.subject-papers-status',['paper_id' => $row->id, 'status' =>$status]).'" class="btn '.$class.' confirm_status" title="'.$title.'">'.$text.'</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Papers List';
        return view('staff.advanced-features.subjects.papers.papers-attempted', $data);
    }

    public function create(Request $request)
    {
        if($request->ajax())
        {
            if(!is_null($request->subject_id) && !empty($request->subject_id) && !is_null($request->paper_name) && !empty($request->paper_name)){
                $where['subject_id'] = $request->subject_id;
                $where['paper_name'] = $request->paper_name;
                if(SubjectPaper::where($where)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }

        $where['subject_study_level'] = 'advanced_level';
        $where['is_deleted'] = 'no';
        $where['subject_staus'] = 'active';
        $data['subjects'] = Subject::where($where)->get();
        $data['title'] = 'Create Subject Paper';
        return view('staff.advanced-features.subjects.papers.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'paper_name' => [
                               'required',
                                Rule::unique('subject_papers')
                                ->where(function($query) use($request){
                                    $query->where('paper_name', $request->paper_name);
                                    $query->where('subject_id', $request->subject_id);
                                })
                            ],
            'paper_compulsary' => 'required',
            'paper_description' => 'required',
        ]);

        $subject = Subject::find($request->subject_id);

        try 
        {
            $ins['subject_id'] = $request->subject_id;
            $ins['paper_code'] = $subject->subject_code.'/'.$request->paper_name;
            $ins['paper_name'] = $request->paper_name;
            $ins['paper_compulsary'] = $request->paper_compulsary;
            $ins['paper_description'] = $request->paper_description;
            $ins['paper_id'] = 'active';
            $ins['is_deleted'] = 'no';
            $ins['created_by'] = Auth::user()->id;
            $ins['updated_by'] = Auth::user()->id;
            SubjectPaper::create($ins);
            return redirect()->route('staff.subject-papers-index-page')->with('success', 'Paper added successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.subject-papers-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function edit(Request $request, $paper_id)
    {
        if($request->ajax())
        {
            if(!is_null($request->subject_id) && !empty($request->subject_id) && !is_null($request->paper_name) && !empty($request->paper_name)){
                $where['subject_id'] = $request->subject_id;
                $where['paper_name'] = $request->paper_name;
                if(SubjectPaper::where($where)->where('id','!=',$paper_id)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }
        $data['paper'] = SubjectPaper::with('subject')->where('id', $paper_id)->first();
        if(is_null($data['paper'])){
            return redirect()->route()->with('error', 'Paper not found');
        }
        $data['title'] = 'Edit Subject Paper';
        $data['subjects'] = Subject::where('subject_study_level','advanced_level')->get();
        return view('staff.advanced-features.subjects.papers.edit', $data);
    }

    public function update(Request $request, $paper_id)
    {
        $request->validate([
            'subject_id' => 'required',
            'paper_name' => [
                               'required',
                                Rule::unique('subject_papers')
                                ->where(function($query) use($request, $paper_id){
                                    $query->where('id','!=', $paper_id);
                                    $query->where('paper_name', $request->paper_name);
                                    $query->where('subject_id', $request->subject_id);
                                })
                            ],
            'paper_compulsary' => 'required',
            'paper_description' => 'required',
        ]);

        $subject = Subject::find($request->subject_id);

        try 
        {
            $upd['subject_id'] = $request->subject_id;
            $upd['paper_code'] = $subject->subject_code.'/'.$request->paper_name;
            $upd['paper_name'] = $request->paper_name;
            $upd['paper_compulsary'] = $request->paper_compulsary;
            $upd['paper_description'] = $request->paper_description;
            $upd['paper_id'] = 'active';
            $upd['is_deleted'] = 'no';
            $upd['created_by'] = Auth::user()->id;
            $upd['updated_by'] = Auth::user()->id;
            $whr['id'] = $paper_id;
            SubjectPaper::where($whr)->update($upd);
            return redirect()->route('staff.subject-papers-index-page')->with('success', 'Paper added successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.subject-papers-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function delete(Request $request, $paper_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            SubjectPaper::where('id',$paper_id)->update($update);
            return redirect()->route('staff.subject-papers-index-page')->with('success', 'Paper removed successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.subject-papers-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $paper_id, $status){
        $update['paper_staus'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            SubjectPaper::where('id',$paper_id)->update($update);
            return redirect()->route('staff.subject-papers-index-page')->with('success', 'Paper status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.subject-papers-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
