<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentSickBay;
use App\Models\StudentDetail;
use Auth;
use DataTables;
use Carbon\Carbon;

class StudentSickBayController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StudentSickBay::with('student')->where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->make(true);
        }
        $data['title'] = 'Papers List';
        return view('admin.advanced-features.students-sick-bay.index', $data);
    }

    public function create(Request $request)
    {
        $where['is_deleted'] = 'no';
        $where['student_staus'] = 'active';
        $data['students'] = StudentDetail::where($where)->get();
        $data['title'] = 'Create Subject Paper';
        return view('admin.advanced-features.students-sick-bay.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'visit_date' => 'required',
            'student_id' => 'required',
            'start_date' => 'required',
            'complain_description' => 'required',
            'action_taken' => 'required',
        ]);

        try 
        {
            $ins['visit_date'] = $request->visit_date;
            $ins['student_id'] = $request->student_id;
            $ins['start_date'] = $request->start_date;
            $ins['complain_description'] = $request->complain_description;
            $ins['action_taken'] = $request->action_taken;
            $ins['student_sick_status'] = 'active';
            $ins['is_deleted'] = 'no';
            $ins['created_by'] = Auth::user()->id;
            $ins['updated_by'] = Auth::user()->id;
            StudentSickBay::create($ins);
            return redirect()->route('admin.students.sick-health-index-page')->with('success', 'Sick bay added successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.students.sick-health-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
