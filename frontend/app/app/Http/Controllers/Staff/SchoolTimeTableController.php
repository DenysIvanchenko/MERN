<?php

namespace App\Http\Controllers\Staff;

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
            $data = SchoolTimeTable::with(['staff','class','subject'])->where('is_deleted', 'no')->where('staff_id',auth()->user()->load(['staffDetails'])->staffDetails->id)->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->editColumn('start_time', function($data){ return date('h:i A', strtotime($data->start_time)); })
                ->editColumn('end_time', function($data){ return date('h:i A', strtotime($data->end_time)); })
                ->addIndexColumn()
                ->make(true);
        }
       $data['title'] = 'Time Table';
       return view('staff.time-table.index',$data);
    }
}
