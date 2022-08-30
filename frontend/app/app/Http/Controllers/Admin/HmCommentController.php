<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DataTables;
use Carbon\Carbon;
use App\Models\HmComment;
use Validator;

class HmCommentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $where['study_level'] = $request->subject_study_level;
            $where['curriculam'] = $request->subject_curriculam;
            $where['is_deleted'] = 'no';
            $data = HmComment::where($where)->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->editColumn('study_level', function($data){ 
                    return ucwords(str_replace('_', " ", $data->study_level));
                })
                ->editColumn('comment', function($data){ 
                    $comment_text_area = '<div><textarea class="form-control comments" data-comment_id="'.$data->id.'">'.$data->comment.'</textarea><span id="result_'.$data->id.'"></span></div>';
                    return $comment_text_area;
                })
                ->rawColumns(['comment'])
                ->addIndexColumn()
                ->make(true);
        }
        $data['title'] = 'Hm Comments List';
        return view('admin.advanced-features.hm-comments.index', $data);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required',
            'comments' => 'required',
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
            $whrClsMrk['id'] = $request->comment_id;
            
            $updateComment = HmComment::where($whrClsMrk)->update([
                'comment' => $request->comments,
                'updated_by' => Auth::user()->id,  
            ]);

            $response = [
                'success' => true,
                'status'  => 'SUCCESS',
                'errors'  => null,
                'message' => 'Comments updated',
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
