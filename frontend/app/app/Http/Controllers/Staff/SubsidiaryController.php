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
use App\Models\SubjectSubsidiary;

class SubsidiaryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SubjectSubsidiary::where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->subsidiary_status == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Subsidiary';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Subsidiary';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('staff.subject-subsidiaries-edit-page',['subsidiary_id' => $row->id]).'"  class="dropdown-item">Edit Subsidiary</a> 
                                        <a href="'.@route('staff.subject-subsidiaries-delete',['subsidiary_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Subsidiary</a> 
                                        <a href="'.@route('staff.subject-subsidiaries-status',['subsidiary_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Papers List';
        return view('staff.advanced-features.subjects.subsidiaries.index', $data);
    }

    public function create(Request $request)
    {
        $data['title'] = 'Create Subject Paper';
        return view('staff.advanced-features.subjects.subsidiaries.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_name' => 'required',
            'short_code' => 'required',
            'number_of_papers' => 'required',
        ]);

        try 
        {
            $alreadyWhr['subject_name'] = $request->subject_name;
            $alreayExists = SubjectSubsidiary::where($alreadyWhr)->count();

            if($alreayExists > 0){
                redirect()->back()
                    ->withInput($request->only('roll_call_name'))
                    ->withErrors([
                        'roll_call_name' => 'Roll call already exists',
                    ]);
            }

            $ins['subject_name'] = $request->subject_name;
            $ins['short_code'] = $request->short_code;
            $ins['number_of_papers'] = $request->number_of_papers;
            $ins['is_deleted'] = 'no';
            $ins['subsidiary_status'] = 'active';
            $ins['created_by'] = Auth::user()->id;
            $ins['updated_by'] = Auth::user()->id;
            SubjectSubsidiary::create($ins);
            return redirect()->route('staff.subject-subsidiaries-index-page')->with('success', 'Subsidiary added successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.subject-subsidiaries-index-page')->with('error', 'Oops somthing went wrong');
        }
    }

    public function edit(Request $request, $subsidiary_id)
    {
        $data['subsidiary'] = SubjectSubsidiary::where('id', $subsidiary_id)->first();
        if(is_null($data['subsidiary'])){
            return redirect()->route('staff.subject-subsidiaries-index-page')->with('error', 'Subsidiary not found');
        }
        $data['title'] = 'Edit Subsidiary';
        return view('staff.advanced-features.subjects.subsidiaries.edit', $data);
    }

    public function update(Request $request, $subsidiary_id)
    {
        $request->validate([
            'subject_name' => 'required',
            'short_code' => 'required',
            'number_of_papers' => 'required',
        ]);

        try 
        {
            $alreadyWhr['subject_name'] = $request->subject_name;
            $alreayExists = SubjectSubsidiary::where('id', '!=', $subsidiary_id)->where($alreadyWhr)->count();

            if($alreayExists > 0){
                redirect()->back()
                    ->withInput($request->only('roll_call_name'))
                    ->withErrors([
                        'roll_call_name' => 'Roll call already exists',
                    ]);
            }

            $ins['subject_name'] = $request->subject_name;
            $ins['short_code'] = $request->short_code;
            $ins['number_of_papers'] = $request->number_of_papers;
            $ins['updated_by'] = Auth::user()->id;
            SubjectSubsidiary::where('id', $subsidiary_id)->update($ins);
            return redirect()->route('staff.subject-subsidiaries-index-page')->with('success', 'Subsidiary udpated successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.subject-subsidiaries-index-page')->with('error', 'Oops somthing went wrong');
        }
    }

    public function delete(Request $request, $subsidiary_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            SubjectSubsidiary::where('id',$subsidiary_id)->update($update);
            return redirect()->route('staff.subject-subsidiaries-index-page')->with('success', 'Subsidiary removed successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.subject-subsidiaries-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $subsidiary_id, $status){
        $update['subsidiary_status'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            SubjectSubsidiary::where('id',$subsidiary_id)->update($update);
            return redirect()->route('staff.subject-subsidiaries-index-page')->with('success', 'Subsidiary status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.subject-subsidiaries-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
