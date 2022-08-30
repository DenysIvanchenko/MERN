<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Auth;
use DataTables;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Models\SubjectPaper;
use App\Models\Subject;
use App\Models\SubjectSubsidiary;
use App\Models\SubjectCombination;

class SubjectCombinationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SubjectCombination::where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addColumn('subjects', function($data,Subject $subject){ 
                    $subject_one = !is_null($subject->where('id', $data->subject_one)->first()) ? $subject->where('id', $data->subject_one)->first()->subject_name : 'NA';
                    $subject_two = !is_null($subject->where('id', $data->subject_one)->first()) ? $subject->where('id', $data->subject_two)->first()->subject_name : 'NA';
                    $subject_three = !is_null($subject->where('id', $data->subject_one)->first()) ? $subject->where('id', $data->subject_three)->first()->subject_name : 'NA';
                    $subjects = $subject_one.'<br>'.$subject_two.'<br>'.$subject_three;
                    return '<div>'.$subjects.'</div>';
                 })
                ->addColumn('subsidiaries', function($data,Subject $subsidiaries){ 
                    $subsidiaries_one = !is_null($subsidiaries->where('id', $data->subsidiary_one)->first()) ? $subsidiaries->where('id', $data->subsidiary_one)->first()->subject_name : 'NA';
                    $subsidiaries_two = !is_null($subsidiaries->where('id', $data->subsidiary_one)->first()) ? $subsidiaries->where('id', $data->subsidiary_two)->first()->subject_name : 'NA';
                    $subsidiaries = $subsidiaries_one.','.$subsidiaries_two;
                    return '<div>'.$subsidiaries.'</div>';
                 })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->combination_status == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Combination';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Combination';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.subject-combinations-edit-page',['combination_id' => $row->id]).'"  class="dropdown-item">Edit Combination</a> 
                                        <a href="'.@route('admin.subject-combinations-delete',['combination_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Combination</a> 
                                        <a href="'.@route('admin.subject-combinations-status',['combination_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action','subjects','subsidiaries'])
                ->make(true);
        }
        $data['title'] = 'Papers List';
        return view('admin.advanced-features.subjects.combinations.index', $data);
    }

    public function create(Request $request)
    {
        $data['title'] = 'Create Subject Paper';
        $data['subject_litst'] = Subject::where('subject_study_level','advanced_level')->where('not_subsidiary', 'yes')->where('is_deleted', 'no')->where('subject_staus', 'active')->get();
        $data['subsiary_litst'] = Subject::where('subject_study_level','advanced_level')->where('not_subsidiary', 'no')->where('is_deleted', 'no')->where('subject_staus', 'active')->get();
        return view('admin.advanced-features.subjects.combinations.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'combination_name' => 'required',
            'subject_one' => 'required',
            'subject_two' => 'required',
            'subject_three' => 'required',
            'type' => 'required',
            'subsidiary_one' => 'required',
            'subsidiary_two' => 'required',
        ]);

        try 
        {
            $alreadyWhr['subject_one'] = $request->subject_one;
            $alreadyWhr['subject_two'] = $request->subject_two;
            $alreadyWhr['subject_three'] = $request->subject_three;
            $alreayExists = SubjectCombination::where($alreadyWhr)->count();

            if($alreayExists > 0){
                redirect()->back()
                    ->withInput($request->only('combination_name'))
                    ->withErrors([
                        'combination_name' => 'Combination already exists',
                    ]);
            }

            $subjects = [
                $request->subject_one,
                $request->subject_two,
                $request->subject_three,
            ];

            if(count(array_unique($subjects))<count($subjects)){
                return redirect()->back()
                    ->withInput($request->all())
                    ->withErrors([
                        'subject_one' => 'Duplicate subjects selection',
                    ]);
            }

            $subsidiaries = [
                $request->subsidiary_one,
                $request->subsidiary_two,
            ];

            if(count(array_unique($subsidiaries))<count($subsidiaries)){
                return redirect()->back()
                    ->withInput($request->all())
                    ->withErrors([
                        'subsidiary_one' => 'Duplicate subsidiary selection',
                    ]);
            }
            
            $ins['combination_name'] = $request->combination_name;
            $ins['subject_one'] = $request->subject_one;
            $ins['subject_two'] = $request->subject_two;
            $ins['subject_three'] = $request->subject_three;
            $ins['type'] = $request->type;
            $ins['subsidiary_one'] = $request->subsidiary_one;
            $ins['subsidiary_two'] = $request->subsidiary_two;
            $ins['is_deleted'] = 'no';
            $ins['combination_status'] = 'active';
            $ins['created_by'] = Auth::user()->id;
            $ins['updated_by'] = Auth::user()->id;
            SubjectCombination::create($ins);
            return redirect()->route('admin.subject-combinations-index-page')->with('success', 'Combination added successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.subject-combinations-index-page')->with('error', 'Oops somthing went wrong');
        }
    }

    public function edit(Request $request, $combination_id)
    {
        $data['combination'] = SubjectCombination::where('id', $combination_id)->first();
        if(is_null($data['combination'])){
            return redirect()->route('admin.subject-combinations-index-page')->with('error', 'Combination not found');
        }
        $data['title'] = 'Edit Combination';
        $data['subject_litst'] = Subject::where('is_deleted', 'no')->where('subject_staus', 'active')->get();
        $data['subsiary_litst'] = SubjectSubsidiary::where('is_deleted', 'no')->where('subsidiary_status', 'active')->get();
        return view('admin.advanced-features.subjects.combinations.edit', $data);
    }

    public function update(Request $request, $combination_id)
    {
        $request->validate([
            'combination_name' => 'required',
            'subject_one' => 'required',
            'subject_two' => 'required',
            'subject_three' => 'required',
            'type' => 'required',
            'subsidiary_one' => 'required',
            'subsidiary_two' => 'required',
        ]);

        try 
        {
            $alreadyWhr['subject_one'] = $request->subject_one;
            $alreadyWhr['subject_two'] = $request->subject_two;
            $alreadyWhr['subject_three'] = $request->subject_three;
            $alreayExists = SubjectCombination::where('id', '!=', $combination_id)->where($alreadyWhr)->count();

            if($alreayExists > 0){
                redirect()->back()
                    ->withInput($request->only('combination_name'))
                    ->withErrors([
                        'combination_name' => 'Combination already exists',
                    ]);
            }

            $subjects = [
                $request->subject_one,
                $request->subject_two,
                $request->subject_three,
            ];

            if(count(array_unique($subjects))<count($subjects)){
                return redirect()->back()
                    ->withInput($request->all())
                    ->withErrors([
                        'subject_one' => 'Duplicate subjects selection',
                    ]);
            }

            $subsidiaries = [
                $request->subsidiary_one,
                $request->subsidiary_two,
            ];

            if(count(array_unique($subsidiaries))<count($subsidiaries)){
                return redirect()->back()
                    ->withInput($request->all())
                    ->withErrors([
                        'subsidiary_one' => 'Duplicate subsidiary selection',
                    ]);
            }
            
            $upd['combination_name'] = $request->combination_name;
            $upd['subject_one'] = $request->subject_one;
            $upd['subject_two'] = $request->subject_two;
            $upd['subject_three'] = $request->subject_three;
            $upd['type'] = $request->type;
            $upd['subsidiary_one'] = $request->subsidiary_one;
            $upd['subsidiary_two'] = $request->subsidiary_two;
            $upd['updated_by'] = Auth::user()->id;

            SubjectCombination::where('id', $combination_id)->update($upd);
            return redirect()->route('admin.subject-combinations-index-page')->with('success', 'Combination udpated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.subject-combinations-index-page')->with('error', 'Oops somthing went wrong');
        }
    }

    public function delete(Request $request, $combination_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            SubjectCombination::where('id',$combination_id)->update($update);
            return redirect()->route('admin.subject-combinations-index-page')->with('success', 'Combination removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.subject-combinations-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function status(Request $request, $subsidiary_id, $status){
        $update['subsidiary_status'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            SubjectSubsidiary::where('id',$subsidiary_id)->update($update);
            return redirect()->route('admin.subject-combinations-index-page')->with('success', 'Subsidiary status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.subject-combinations-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
