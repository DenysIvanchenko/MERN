<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolTimeTable;
use App\Models\StaffDetail;
use App\Models\SubjectClass;
use App\Models\Subject;
use App\Models\Stream;
use App\Models\StaffLoad;
use Auth;
use DataTables;
use Carbon\Carbon;

class SchoolTimeTableController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SchoolTimeTable::with(['staff','class','subject'])->where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->editColumn('start_time', function($data){ return date('h:i A', strtotime($data->start_time)); })
                ->editColumn('end_time', function($data){ return date('h:i A', strtotime($data->end_time)); })
                ->addColumn('stream', function($data, Stream  $stream){
                    if($data->stream_id == 'all_streams'){
                        return 'All Streams';
                    }else{
                        return $stream->find($data->stream_id)->stream_name;
                    }
                })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->timetable_status == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Time Table';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Time Table';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.time-table-edit-page',['time_table_id' => $row->id]).'"  class="dropdown-item">Edit Time Table</a> <a href="'.@route('admin.time-table-delete',['time_table_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Time Table</a> <a href="'.@route('admin.time-table-status',['time_table_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
       $data['title'] = null;
       return view('admin.time-table.index',$data);
    }

    public function create(Request $request)
    {
        $data['staff_lists'] = StaffDetail::where('is_deleted','no')->where('staff_staus','active')->get();
        $data['title'] = 'Create Time Table';
        return view('admin.time-table.create',$data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'day' => 'required',
            'staff_id' => 'required',
            'class_id' => 'required',
            'stream_id' => 'required',
            'subject_id' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        try 
        {
            
            $ins['day'] = $request->day;
            $ins['staff_id'] = $request->staff_id;
            $ins['class_id'] = $request->class_id;
            $ins['stream_id'] = $request->stream_id;
            $ins['subject_id'] = $request->subject_id;
            $ins['start_time'] = $request->start_time;
            $ins['end_time'] = $request->end_time;
            $ins['timetable_status'] = 'active';
            $ins['is_deleted'] = 'no';
            $ins['created_by'] =  Auth::user()->id;
            $ins['updated_by'] =  Auth::user()->id;
            SchoolTimeTable::create($ins);
            return redirect()->route('admin.time-table-index-page')->with('success','Time table added successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.time-table-index-page')->with('error','Oops something went wrong');
        }
    }

    public function edit(Request $request,$time_table_id)
    {
        $data['time_table'] = SchoolTimeTable::where('id', $time_table_id)->first();
        if(is_null($data['time_table'])){
            return redirect()->route('admin.time-table-index-page')->with('error','Time table not found');
        }
        $data['staff_lists'] = StaffDetail::where('is_deleted','no')->where('staff_staus','active')->get();
        $where_load['staff_id'] = $data['time_table']->staff_id;
        $where_load['load_status'] = 'active';
        $where_load['is_deleted'] = 'no';
        $loads = StaffLoad::where($where_load)->get();
        $class_ids = array();
        if($loads->count() > 0){
            foreach($loads as $load){
                $class_ids[$load->class_id] = $load->class_id;    
            }
        }
        $data['class_lists'] = SubjectClass::whereIn('id',$class_ids)->where('class_status', 'active')->where('is_deleted', 'no')->get();
        $data['stream_lists'] = Stream::where('class_id', $data['time_table']->class_id)->where('is_deleted','no')->where('stream_status','active')->get();
        $subject_ids = array();
        if($loads->count() > 0){
            foreach($loads as $load1){  
                $subject_ids[$load1->subject_id] = $load1->subject_id;    
            }
        }
        $data['subject_lists'] = Subject::whereIn('id',$subject_ids)->where('subject_staus', 'active')->where('is_deleted', 'no')->get();
        $data['title'] = 'Create Time Table';
        return view('admin.time-table.edit',$data);
    }

    public function update(Request $request,$time_table_id)
    {
        $request->validate([
            'day' => 'required',
            'staff_id' => 'required',
            'class_id' => 'required',
            'stream_id' => 'required',
            'subject_id' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        try 
        {
            
            $upd['day'] = $request->day;
            $upd['staff_id'] = $request->staff_id;
            $upd['class_id'] = $request->class_id;
            $upd['stream_id'] = $request->stream_id;
            $upd['subject_id'] = $request->subject_id;
            $upd['start_time'] = $request->start_time;
            $upd['end_time'] = $request->end_time;
            $upd['updated_by'] =  Auth::user()->id;
            SchoolTimeTable::where('id', $time_table_id)->update($upd);
            return redirect()->route('admin.time-table-index-page')->with('success','Time table updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.time-table-index-page')->with('error','Oops something went wrong');
        }
    }

    public function delete(Request $request, $time_table_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            SchoolTimeTable::where('id',$time_table_id)->update($update);
            return redirect()->route('admin.time-table-index-page')->with('success', 'Timetable removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.time-table-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $time_table_id, $status){
        $update['timetable_status'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            SchoolTimeTable::where('id',$time_table_id)->update($update);
            return redirect()->route('admin.time-table-index-page')->with('success', 'Timetable status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.time-table-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
