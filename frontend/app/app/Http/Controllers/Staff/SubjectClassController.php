<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\ClassNextTermFee;
use App\Models\SubjectClass;
use DataTables;
use Carbon\Carbon;
use Validator;

class SubjectClassController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:staff.class-page|staff.class-class-curriculam-settings-page|staff.class-class-curriculam-update|staff.class-fees-settings-page|staff.class-fees-update|staff.class-create-page|staff.class-store|staff.class-edit-page|staff.class-update|staff.class-delete|staff.class-status']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SubjectClass::where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->class_status == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Class';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Class';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('staff.class-edit-page',['class_id' => $row->id]).'"  class="dropdown-item">Edit Class</a> <a href="'.@route('staff.class-delete',['class_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Class</a> <a href="'.@route('staff.class-status',['class_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Class List';
        return view('staff.subject-class.index', $data);
    }

    public function create(Request $request)
    {
        $data['title'] = 'Create Class';
        return view('staff.subject-class.create', $data);
    }

    public function store(Request $request)
    {
        if($request->ajax())
        {
            if(!is_null($request->class_name) && !empty($request->class_name)){
                if(SubjectClass::where('class_name', $request->class_name)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }

            if(!is_null($request->class_prefix) && !empty($request->class_prefix)){
                if(SubjectClass::where('class_prefix', $request->class_prefix)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }
        
        $request->validate([
            'class_name' => 'required|unique:classes,class_name',
            'class_prefix'=> 'required|unique:classes,class_prefix',
            'class_level' => 'required',
        ]);

        $ins['class_name'] = $request->class_name;
        $ins['class_prefix'] = $request->class_prefix;
        $ins['class_level'] = $request->class_level;
        $ins['class_status'] = 'active';
        $ins['is_deleted'] = 'no';
        $ins['created_by'] = Auth::user()->id;
        $ins['updated_by'] = Auth::user()->id;

        try 
        {
            SubjectClass::create($ins);
            return redirect()->route('staff.class-page')->with('success', 'Class created successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.class-page')->with('error', 'Oops something went wrong');
        }
    }

    public function edit(Request $reques, $class_id)
    {
        $data['class'] = SubjectClass::where('id', $class_id)->first();
        if(is_null($data['class'])){
            return redirect()->route('staff.class-page')->with('error', 'Class not found');
        }
        $data['title'] = 'Create Class';
        return view('staff.subject-class.edit', $data);
    }

    public function update(Request $request, $class_id)
    {
        if($request->ajax())
        {
            if(!is_null($request->class_name) && !empty($request->class_name)){
                if(SubjectClass::where('class_name', $request->class_name)->where('id','!=', $class_id)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }

            if(!is_null($request->class_prefix) && !empty($request->class_prefix)){
                if(SubjectClass::where('class_prefix', $request->class_prefix)->where('id','!=', $class_id)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }

        $request->validate([
            'class_name'  => 'required|unique:classes,class_name,'.$class_id,
            'class_prefix' => 'required|unique:classes,class_prefix,'.$class_id,
            'class_level' => 'required',
        ]);

        $upd['class_name'] = $request->class_name;
        $upd['class_prefix'] = $request->class_prefix;
        $upd['class_level'] = $request->class_level;
        $upd['updated_by'] = Auth::user()->id;

        try 
        {
            SubjectClass::where('id', $class_id)->update($upd);
            return redirect()->route('staff.class-page')->with('success', 'Class updated successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.class-page')->with('error', 'Oops something went wrong');
        }
    }

    public function delete(Request $request, $class_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            SubjectClass::where('id',$class_id)->update($update);
            return redirect()->route('staff.class-page')->with('success', 'Class removed successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.class-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $class_id, $status){
        $update['class_status'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            SubjectClass::where('id',$class_id)->update($update);
            return redirect()->route('staff.class-page')->with('success', 'Class status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.class-page')->with('error', 'Oops something went wrong');
        }
    }

    public function curiculamSettings(Request $request)
    {
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_level','ordinary_level')->latest()->get();
        $data['title'] = 'Class Curiiculam Settings';
        return view('staff.subject-class.curiculamSettings', $data);
    }

    public function curiculamSettingsUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required',
            'curriculam' => 'required',
        ]);
   
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
            $whrCls['id'] = $request->class_id;
            
            $updateClassMark = SubjectClass::where($whrCls)->update([
                'class_curriculam' => $request->curriculam,
                'updated_by' => Auth::user()->id,  
            ]);

            $response = [
                'success' => true,
                'status'  => 'SUCCESS',
                'errors'  => null,
                'message' => 'Curriculam updated for the class',
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

    public function classFees(Request $request)
    {
        $data['class_lists'] = SubjectClass::with('next_term_fee')->where('is_deleted', 'no')->latest()->get();
        $data['title'] = 'Class Next Term Fees';
        return view('staff.subject-class.classFees', $data);
    }

    public function classFeesUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required',
            'fee_type' => 'required',
            'fee_value' => 'required',
        ]);
   
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
            $whrCls['class_id'] = $request->class_id;
            
            $updateClassMark = ClassNextTermFee::updateOrcreate($whrCls,[
                $request->class_id => $request->class_id,
                $request->fee_type => $request->fee_value,
                'created_by' => Auth::user()->id,  
                'updated_by' => Auth::user()->id,  
                'fee_status' =>'active',  
            ]);

            $response = [
                'success' => true,
                'status'  => 'SUCCESS',
                'errors'  => null,
                'message' => 'Fees updated for the class',
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
}
