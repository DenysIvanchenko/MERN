<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DataTables;
use Carbon\Carbon;
use App\Models\StaffAttendance;
use App\Models\StaffDetail;
use App\Models\StudentAttendance;
use App\Models\StudentRollCall;
use App\Models\StudentDetail;
use App\Models\StaffCategory;
use App\Models\Stream;
use App\Models\SubjectClass;

class AttendanceController extends Controller
{
    public function staffAttendance(Request $request)
    {
        if(count($_GET) > 0){
            $whrStaff['staff_category_id'] = $request->staff_category;
            $whrStaff['staff_staus'] = 'active';
            $whrStaff['is_deleted'] = 'no';
            $data['staff_lists'] = StaffDetail::with('staff_attendance')->where($whrStaff)->get();
        }
        $data['title'] = 'Staff Attendance';
        $whrCat['category_staus'] = 'active';
        $whrCat['is_deleted'] = 'no';
        $data['staff_categories'] = StaffCategory::where($whrCat)->get();
        return view('admin.attendance.staffAttendance', $data);
    }

    public function storeStaffAttendance(Request $request)
    {
        $request->validate([
            'attendance_date' => 'required',
            'staff_attenance.*' => 'required',
            'staff_attenance_comments.*' => 'required',
        ]);

        try 
        {
            $staff_attenances = $request->staff_attenance;
            $staff_attenance_comments = $request->staff_attenance_comments;
            foreach ($staff_attenances as $key => $value) {
                
                $whrAtt['date'] = $request->attendance_date;    
                $whrAtt['staff_id'] = $key;

                $insOrupd['date'] = $request->attendance_date;
                $insOrupd['staff_id'] = $key;
                $insOrupd['comments'] = $staff_attenance_comments[$key];
                $insOrupd['attendance_status'] = $value;
                $insOrupd['is_deleted'] = 'no';
                $insOrupd['created_by'] = Auth::user()->id;
                $insOrupd['updated_by'] = Auth::user()->id;

                StaffAttendance::updateOrCreate($whrAtt,$insOrupd);
            }
            return redirect()->route('admin.staff-attendance-page')->with('success','Attendance updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.staff-attendance-page')->with('error','Oops something went wrong');
        }
    }

    public function viewStaffAttendance(Request $request)
    {
        if(count($_GET) > 0){
            $whrStaff['is_deleted'] = 'no';
            $data['staff_lists'] = StaffAttendance::with('staff')->whereBetween('date', [$request->attendance_date_from, $request->attendance_date_to])->where($whrStaff)->get();
        }
        $data['title'] = 'Staff Attendance';
        return view('admin.attendance.viewStaffAttendance', $data);
    }

    public function studentAttendance(Request $request)
    {
        if(count($_GET) > 0){
            $whrStu['class_id'] = $request->class_id;
            $whrStu['stram_id'] = $request->stram_id;
            $whrStu['student_staus'] = 'active';
            $whrStu['is_deleted'] = 'no';
            $data['student_lists'] = StudentDetail::where($whrStu)->get();
        }
        $data['title'] = 'Student Attendance';
        $data['student_roll_calls'] = StudentRollCall::where('is_deleted', 'no')->where('roll_call_status', 'active')->get();
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status', 'active')->get();
        return view('admin.attendance.studentAttendance',$data);
    }

    public function storeStudentAttendance(Request $request)
    {
        $request->validate([
            'attendance_date' => 'required',
            'student_attenance.*' => 'required',
            'student_attenance_comments.*' => 'required',
        ]);

        try 
        {
            $student_attenances = $request->student_attenance;
            $student_attenance_comments = $request->student_attenance_comments;
            foreach ($student_attenances as $key => $value) {
                
                $whrAtt['date'] = $request->attendance_date;    
                $whrAtt['student_id'] = $key;
                if(isset($request->roll_call_id) && !empty($request->roll_call_id)){
                    $insOrupd['roll_call_id'] = $request->roll_call_id;
                }
                $insOrupd['date'] = $request->attendance_date;
                $insOrupd['student_id'] = $key;
                $insOrupd['comments'] = $student_attenance_comments[$key];
                $insOrupd['attendance_status'] = $value;
                $insOrupd['is_deleted'] = 'no';
                $insOrupd['created_by'] = Auth::user()->id;
                $insOrupd['updated_by'] = Auth::user()->id;

                StudentAttendance::updateOrCreate($whrAtt,$insOrupd);
            }
            return redirect()->route('admin.student-attendance-page')->with('success','Attendance updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.student-attendance-page')->with('error','Oops something went wrong');
        }
    }

    public function viewStudentAttendance(Request $request)
    {
        if(count($_GET) > 0){
            if(isset($request->roll_call_id) && !empty($request->roll_call_id)){
                $whrStaff['roll_call_id'] = $request->roll_call_id;
            }
            $whrStaff['is_deleted'] = 'no';
            $data['staff_lists'] = StudentAttendance::with(['student','roll_call'])->whereBetween('date', [$request->attendance_date_from, $request->attendance_date_from])->where($whrStaff)->whereHas('student',function($query) use ($request){ 
                $query->where('class_id', $request->class_id); 
                if(isset($request->stram_id) && !empty($request->stram_id)){
                    $query->where('stram_id',$request->stram_id);
                }
            })->get();
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status', 'active')->get();
        $data['student_roll_calls'] = StudentRollCall::where('is_deleted', 'no')->where('roll_call_status', 'active')->get();
        $data['title'] = 'Student Attendance';
        return view('admin.attendance.viewStudentAttendance', $data);
    }

    public function studentAttendanceReport(Request $request)
    {
        if(count($_GET) > 0){
            if(isset($request->roll_call_id) && !empty($request->roll_call_id)){
                $whrStaff['roll_call_id'] = $request->roll_call_id;
            }
            $whrStaff['is_deleted'] = 'no';
            $data['staff_lists'] = StudentAttendance::with(['student','roll_call'])->whereBetween('date', [$request->attendance_date_from, $request->attendance_date_to])->where($whrStaff)->whereHas('student',function($query) use ($request){ 
                $query->where('class_id', $request->class_id); 
                if(isset($request->stram_id) && !empty($request->stram_id)){
                    $query->where('stram_id',$request->stram_id);
                }
            })->get();
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status', 'active')->get();
        $data['student_roll_calls'] = StudentRollCall::where('is_deleted', 'no')->where('roll_call_status', 'active')->get();
        $data['title'] = 'Student Attendance';
        return view('admin.attendance.studentAttendanceReport', $data);
    }

    public function rollCallsIndex(Request $request)
    {
        if ($request->ajax()) {
            $data = StudentRollCall::where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->roll_call_status == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Roll Call';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Roll Call';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.student-attendance-roll-call-edit-page',['roll_call_id' => $row->id]).'"  class="dropdown-item">Edit Roll Call</a> 
                                        <a href="'.@route('admin.student-attendance-roll-call-delete',['roll_call_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Roll Call</a> 
                                        <a href="'.@route('admin.student-attendance-roll-call-status',['roll_call_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Roll Call List';
        return view('admin.attendance.roll-calls.index', $data);
    }

    public function rollCallsCreate(Request $request)
    {
        $data['title'] = 'Create Roll Call';
        return view('admin.attendance.roll-calls.create', $data);
    }

    public function rollCallsStore(Request $request)
    {
        $request->validate([
            'roll_call_name' => 'required|unique:student_roll_calls,roll_call_name',
            'roll_call_description' => 'required',
        ]);

        try 
        {
            $ins['roll_call_name'] = $request->roll_call_name;
            $ins['roll_call_description'] = $request->roll_call_description;
            $ins['is_deleted'] = 'no';
            $ins['subsidiary_status'] = 'active';
            $ins['created_by'] = Auth::user()->id;
            $ins['updated_by'] = Auth::user()->id;
            StudentRollCall::create($ins);
            return redirect()->route('admin.student-attendance-roll-call-index-page')->with('success', 'Roll call added successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.student-attendance-roll-call-index-page')->with('error', 'Oops somthing went wrong');
        }
    }

    public function rollCallsEdit(Request $request, $roll_call_id)
    {
        $data['roll_call'] = StudentRollCall::where('id', $roll_call_id)->first();
        if(is_null($data['roll_call'])){
            return redirect()->route('admin.student-attendance-roll-call-index-page')->with('error', 'Roll call not found');
        }
        $data['title'] = 'Edit Roll Call';
        return view('admin.attendance.roll-calls.edit', $data);
    }

    public function rollCallsUpdate(Request $request, $roll_call_id)
    {
        $request->validate([
            'roll_call_name' => 'required|unique:student_roll_calls,roll_call_name,'.$roll_call_id,
            'roll_call_description' => 'required',
        ]);

        try 
        {
            $alreadyWhr['roll_call_name'] = $request->roll_call_name;
            $alreayExists = StudentRollCall::where($alreadyWhr)->where('id', '!=', $roll_call_id)->count();

            if($alreayExists > 0){
                redirect()->back()
                    ->withInput($request->only('roll_call_name'))
                    ->withErrors([
                        'roll_call_name' => 'Roll call already exists',
                    ]);
            }

            $ins['roll_call_name'] = $request->roll_call_name;
            $ins['roll_call_description'] = $request->roll_call_description;
            $ins['updated_by'] = Auth::user()->id;
            StudentRollCall::where('id',$roll_call_id)->update($ins);
            return redirect()->route('admin.student-attendance-roll-call-index-page')->with('success', 'Roll call added successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.student-attendance-roll-call-index-page')->with('error', 'Oops somthing went wrong');
        }
    }

    public function rollCallsDelete(Request $request, $roll_call_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            StudentRollCall::where('id',$roll_call_id)->update($update);
            return redirect()->route('admin.student-attendance-roll-call-index-page')->with('success', 'Subsidiary removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.student-attendance-roll-call-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function rollCallsStatus(Request $request, $roll_call_id, $status){
        $update['roll_call_status'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            StudentRollCall::where('id',$roll_call_id)->update($update);
            return redirect()->route('admin.student-attendance-roll-call-index-page')->with('success', 'Subsidiary status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.student-attendance-roll-call-index-page')->with('error', 'Oops something went wrong');
        }
    }

}
