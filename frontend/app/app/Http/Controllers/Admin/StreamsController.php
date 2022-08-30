<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Stream;
use App\Models\SubjectClass;
use DataTables;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StreamsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Stream::with('class_details')->where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->stream_status == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Stream';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Stream';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.streams-edit-page',['stream_id' => $row->id]).'"  class="dropdown-item">Edit Stream</a> <a href="'.@route('admin.streams-delete',['stream_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Stream</a> <a href="'.@route('admin.streams-status',['stream_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Streams List';
        return view('admin.streams.index', $data);
    }

    public function create(Request $request)
    {
        if($request->ajax()){
            if(!is_null($request->class_id) && !empty($request->class_id) && !is_null($request->stream_name) && !empty($request->stream_name)){
                if(Stream::where('stream_name', $request->stream_name)->where('class_id', $request->class_id)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }

        $data['classes'] = SubjectClass::where('is_deleted', 'no')->where('class_status', 'active')->get();
        $data['title'] = 'Create Stream';
        return view('admin.streams.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'stream_name' =>[
                               'required',
                                Rule::unique('streams')
                                ->where(function($query) use($request){
                                    $query->where('class_id', '=', $request->class_id);
                                    $query->where('stream_name', $request->stream_name);
                                })
                            ],
        ]);

        $ins['class_id'] = $request->class_id;
        $ins['stream_name'] = $request->stream_name;
        $ins['is_deleted'] = 'no';
        $ins['stream_status'] = 'active';
        $ins['created_by'] = Auth::user()->id;
        $ins['updated_by'] = Auth::user()->id;

        try 
        {
            Stream::create($ins);
            return redirect()->route('admin.streams-page')->with('success', 'Stream created successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.streams-page')->with('error', 'Oops something went wrong');
        }
    }

    public function edit(Request $request, $stream_id)
    {
        if($request->ajax()){
            if(!is_null($request->stream_name) && !empty($request->stream_name)){
                if(Stream::where('stream_name', $request->stream_name)->where('class_id', $request->class_id)->where('id', '!=', $stream_id)->count() > 0){
                    return 'false';
                }else{
                    return 'true';
                }
            }
        }
        $data['stream'] = Stream::where('id', $stream_id)->first();
        if(is_null($data['stream'])){
            return redirect()->route('admin.streams-page')->with('error', 'Stream not found');
        }
        $data['classes'] = SubjectClass::where('is_deleted', 'no')->where('class_status', 'active')->get();
        $data['title'] = 'Create Stream';
        return view('admin.streams.edit', $data);
    }

    public function update(Request $request, $stream_id)
    {
        $request->validate([
            'class_id' => 'required',
            'stream_name' =>[
                               'required',
                                Rule::unique('streams')
                                ->where(function($query) use($request,$stream_id){
                                    $query->where('class_id', '=', $request->class_id);
                                    $query->where('id', '!=', $stream_id);
                                    $query->where('stream_name', $request->stream_name);
                                })
                            ],
        ]);

        $upd['class_id'] = $request->class_id;
        $upd['stream_name'] = $request->stream_name;
        $upd['created_by'] = Auth::user()->id;
        $upd['updated_by'] = Auth::user()->id;

        try 
        {
            Stream::where('id', $stream_id)->update($upd);
            return redirect()->route('admin.streams-page')->with('success', 'Stream updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.streams-page')->with('error', 'Oops something went wrong');
        }
    }


    public function delete(Request $request, $class_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            Stream::where('id',$class_id)->update($update);
            return redirect()->route('admin.streams-page')->with('success', 'Stream removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.streams-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $class_id, $status){
        $update['stream_status'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            Stream::where('id',$class_id)->update($update);
            return redirect()->route('admin.streams-page')->with('success', 'Stream status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.streams-page')->with('error', 'Oops something went wrong');
        }
    }
}
