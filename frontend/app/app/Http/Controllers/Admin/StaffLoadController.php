<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Stream;
use App\Models\SubjectClass;
use App\Models\Subject;
use App\Models\StaffDetail;
use App\Models\StaffLoad;
use App\Models\SubjectPaper;
use DataTables;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StaffLoadController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StaffLoad::with(['staff','class','subject','paper','stream'])->where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $actionBtn = '
                            <a href="'.@route('admin.staff-loads-delete',['staff_load_id' => $row->id]).'" class="btn btn-danger btn-sm confirm_delete">Remove Load</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Staff Load List';
        return view('admin.advanced-features.staffs.staff-loads.index', $data);
    }

    public function create(Request $request)
    {
        $data['title'] = 'Add Staff Load';
        $data['staffs'] = StaffDetail::where('is_deleted', 'no')->where('staff_staus', 'active')->get();
        $data['classes'] = SubjectClass::where('is_deleted', 'no')->where('class_status', 'active')->get();
        return view('admin.advanced-features.staffs.staff-loads.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id'  => 'required',
            'class_id'  => 'required',
            'subject_id' => 'required'
        ]);

        try 
        {
            $whrExi['staff_id'] = $request->staff_id;
            $whrExi['class_id'] = $request->class_id;
            $whrExi['subject_id'] = $request->subject_id;
            if($request->stream_id && !is_null($request->stream_id)){
                if($request->stream_id == 'all_streams'){
                    $whrExi['all_streams'] = 'yes';
                }else{
                    $whrExi['stream_id'] = $request->stream_id;
                }
            }
            if($request->paper_id && !is_null($request->paper_id)){
                if($request->paper_id == 'all_papers'){
                    $whrExi['all_papers'] = 'yes';
                }else{
                    $whrExi['paper_id'] = $request->paper_id;
                }
            }
            $loadExists = StaffLoad::where($whrExi)->count();
            if($loadExists > 0)
            {
                return redirect()->back()
                ->withInput($request->only('subject_id'))
                ->withErrors([
                    'subject_id' => 'This subject already exists for this staff.',
                ]);         
            }
            $ins['staff_id'] = $request->staff_id;
            $ins['class_id'] = $request->class_id;
            $ins['subject_id'] = $request->subject_id;
            if($request->paper_id == 'all_papers'){
                $ins['all_papers'] = 'yes';
            }else{
                $ins['paper_id'] = $request->paper_id;
            }
            if($request->stream_id == 'all_streams'){
                $ins['all_streams'] = 'yes';
            }else{
                $ins['stream_id'] = $request->stream_id;
            }
            $ins['is_deleted'] = 'no';
            $ins['created_by'] = Auth::user()->id;
            $insert = StaffLoad::create($ins);
            return redirect()->route('admin.staff-loads-index-page')->with('success', 'Staff load added successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.sstaff-loads-create-page')->with('error', 'Oops something went wrong');
        }
    }

    public function getSubjects(Request $request)
    {
        $where['subject_curriculam'] = $request->study_level;
        $where['subject_staus'] = 'active';
        $where['is_deleted'] = 'no';
        $subjects = Subject::where($where)->get();
        $return = '<option value="">Select</option>';
        if($subjects->count() > 0){
            foreach ($subjects as $value) {
                $return.='<option value="'.$value->id.'">'.$value->subject_name.'</option>';
            }
        }
        return $return;
    }

    public function getStreams(Request $request)
    {
        $where['class_id'] = $request->class_id;
        $where['stream_status'] = 'active';
        $where['is_deleted'] = 'no';
        $streams = Stream::where($where)->get();
        $return = '<option value="">Select</option>';
        if($streams->count() > 0){
            $return.= '<option value="all_streams">All Streams</option>';
            foreach ($streams as $value) {
                $return.='<option value="'.$value->id.'">'.$value->stream_name.'</option>';
            }
        }
        return $return;
    }

    public function getPapers(Request $request)
    {
        $where['subject_id'] = $request->subject_id;
        $where['paper_staus'] = 'active';
        $where['is_deleted'] = 'no';
        $papers = SubjectPaper::where($where)->get();
        $return = '<option value="">Select</option>';
        if($papers->count() > 0){
            $return.= '<option value="all_papers">All Papers</option>';
            foreach ($papers as $value) {
                $return.='<option value="'.$value->id.'"> Paper '.$value->paper_name.'</option>';
            }
        }
        return $return;
    }

    public function delete(Request $request, $staff_load_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            StaffLoad::where('id',$staff_load_id)->update($update);
            return redirect()->route('admin.staff-loads-index-page')->with('success', 'Staff load removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.staff-loads-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
