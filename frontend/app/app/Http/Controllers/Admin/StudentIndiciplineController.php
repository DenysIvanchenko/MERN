<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DataTables;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Models\StudentIndiciplineCase;
use App\Models\StudentIndiciplineCategory;
use App\Models\StudentIndiciplineRating;
use App\Models\StudentDetail;
use App\Models\StaffDetail;

class StudentIndiciplineController extends Controller
{
    public function ratingsIndex(Request $request)
    {
        if ($request->ajax()) {
            $data = StudentIndiciplineRating::where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->rating_category_status == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Rating';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Rating';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.indecipline-ratings-edit-page',['ratings_id' => $row->id]).'"  class="dropdown-item">Edit Rating</a> 
                                        <a href="'.@route('admin.indecipline-ratings-delete',['ratings_id' => $row->id]).'" class="dropdown-item confirm_delete">Delete Rating</a> 
                                        <a href="'.@route('admin.indecipline-ratings-status',['ratings_id' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Papers List';
        return view('admin.advanced-features.student-indicipline.ratings.index', $data);
    }

    public function createRating(Request $request)
    {
        $data['title'] = 'Create Rating';
        return view('admin.advanced-features.student-indicipline.ratings.create', $data);
    }

    public function storeRating(Request $request)
    {
        $request->validate([
            'rating_category' => 'required|unique:student_indicipline_ratings,rating_category',
        ]);

        try 
        {
            $ins['rating_category'] = $request->rating_category;    
            $ins['rating_category_status'] = 'active';    
            $ins['is_deleted'] = 'no';    
            $ins['created_by'] = Auth::user()->id;    
            $ins['updated_by'] = Auth::user()->id;
            StudentIndiciplineRating::create($ins);
            return redirect()->route('admin.indecipline-ratings-index-page')->with('success', 'Rating added successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.indecipline-ratings-index-page')->with('error', 'Oops somthing went wrong');
        }
    }

    public function editRating(Request $request, $rating_id)
    {
        $data['rating'] = StudentIndiciplineRating::where('id',$rating_id)->first();
        if(is_null($data['rating'])){
            return redirect()->route('admin.indecipline-ratings-index-page')->with('error', 'Rating not found');
        }
        $data['title'] = 'Edit Rating';
        return view('admin.advanced-features.student-indicipline.ratings.edit', $data);
    }

    public function updateRating(Request $request, $rating_id)
    {
        $request->validate([
            'rating_category' => 'required|unique:student_indicipline_ratings,rating_category,'.$rating_id,
        ]);

        try 
        {
            $ins['rating_category'] = $request->rating_category;      
            $ins['updated_by'] = Auth::user()->id;
            StudentIndiciplineRating::where('id',$rating_id)->update($ins);   
            return redirect()->route('admin.indecipline-ratings-index-page')->with('success', 'Rating updated successfully'); 
        } catch (Exception $e) {
            return redirect()->route('admin.indecipline-ratings-index-page')->with('error', 'Oops somthing went wrong');
        }
    }

    public function deleteRating(Request $request, $rating_id){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            StudentIndiciplineRating::where('id',$rating_id)->update($update);
            return redirect()->route('admin.indecipline-ratings-index-page')->with('success', 'Rating removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.indecipline-ratings-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function ratingStatus(Request $request, $rating_id, $status){
        $update['rating_category_status'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            StudentIndiciplineRating::where('id',$rating_id)->update($update);
            return redirect()->route('admin.indecipline-ratings-index-page')->with('success', 'Rating status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.indecipline-ratings-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function categoryIndex(Request $request)
    {
        if ($request->ajax()) {
            $data = StudentIndiciplineCategory::where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->indicipline_category_status == 'active'){
                        $status = 'inactive';
                        $class = 'btn-danger';
                        $icon = 'fa fa-times';
                        $title = 'Click to disable';
                        $text = 'Disable Category';
                    }else{
                        $status = 'active';
                        $class = 'btn-success';
                        $icon = 'fa fa-check';
                        $title = 'Click to enable';
                        $text = 'Enable Category';
                    }
                    $actionBtn = '
                            <button class="btn btn-primary btn-border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action</button>
                                    <div class="dropdown-menu">
                                        <a href="'.@route('admin.indecipline-category-edit-page',['category_ids' => $row->id]).'"  class="dropdown-item">Edit Category</a> 
                                        <a href="'.@route('admin.indecipline-category-delete',['category_ids' => $row->id]).'" class="dropdown-item confirm_delete">Delete Category</a> 
                                        <a href="'.@route('admin.indecipline-category-status',['category_ids' => $row->id, 'status' =>$status]).'" class="dropdown-item confirm_status" title="'.$title.'">'.$text.'</a>
                                    </div>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $data['title'] = 'Papers List';
        return view('admin.advanced-features.student-indicipline.category.index', $data);
    }

    public function createCategory(Request $request)
    {
        $data['title'] = 'Create Category';
        return view('admin.advanced-features.student-indicipline.category.create', $data);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'category_title' => 'required|unique:student_indicipline_categories,category_title',
            'category_punishment' => 'required',
        ]);

        try 
        {
            $ins['category_title'] = $request->category_title;    
            $ins['category_punishment'] = $request->category_punishment;    
            $ins['indicipline_category_status'] = 'active';    
            $ins['is_deleted'] = 'no';    
            $ins['created_by'] = Auth::user()->id;    
            $ins['updated_by'] = Auth::user()->id;
            StudentIndiciplineCategory::create($ins);
            return redirect()->route('admin.indecipline-category-index-page')->with('success', 'Category added successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.indecipline-category-index-page')->with('error', 'Oops somthing went wrong');
        }
    }

    public function editCategory(Request $request, $rating_id)
    {
        $data['category'] = StudentIndiciplineCategory::where('id',$rating_id)->first();
        if(is_null($data['category'])){
            return redirect()->route('admin.indecipline-ratings-index-page')->with('error', 'Category not found');
        }
        $data['title'] = 'Edit Rating';
        return view('admin.advanced-features.student-indicipline.category.edit', $data);
    }

    public function updateCategory(Request $request, $category_ids)
    {
        $request->validate([
            'category_title' => 'required|unique:student_indicipline_categories,category_title,'.$category_ids,
            'category_punishment' => 'required',
        ]);

        try 
        {
            $ins['category_title'] = $request->category_title;    
            $ins['category_punishment'] = $request->category_punishment;    
            $ins['updated_by'] = Auth::user()->id;
            StudentIndiciplineCategory::where('id',$category_ids)->update($ins);
            return redirect()->route('admin.indecipline-category-index-page')->with('success', 'Category updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.indecipline-category-index-page')->with('error', 'Oops somthing went wrong');
        }
    }

    public function deleteCategory(Request $request, $category_ids){
        $update['is_deleted'] = 'yes';
        $update['updated_by'] = Auth::user()->id;
        $update['deleted_by'] = Auth::user()->id;
        $update['deleted_at'] = date('Y-m-d H:i:s');
        try {
            StudentIndiciplineCategory::where('id',$category_ids)->update($update);
            return redirect()->route('admin.indecipline-category-index-page')->with('success', 'Category removed successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.indecipline-category-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function categoryStatus(Request $request, $category_ids, $status){
        $update['indicipline_category_status'] = $status;
        $update['updated_by'] = Auth::user()->id;
        try {
            StudentIndiciplineCategory::where('id',$category_ids)->update($update);
            return redirect()->route('admin.indecipline-category-index-page')->with('success', 'Category status updated successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.indecipline-category-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function caseIndex(Request $request)
    {
        if ($request->ajax()) {
            $data = StudentIndiciplineCase::with(['student','staff','category','rating'])->where('is_deleted', 'no')->latest()->get();
            return Datatables::of($data)
                ->editColumn('created_at', function($data){ $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d-m-Y'); return $formatedDate; })
                ->addIndexColumn()
                ->make(true);
        }
        $data['title'] = 'Papers List';
        return view('admin.advanced-features.student-indicipline.index', $data);
    }

    public function createCase(Request $request)
    {
        $data['title'] = 'Papers List';
        $data['students'] = StudentDetail::where('is_deleted','no')->where('student_staus','active')->get();
        $data['category'] = StudentIndiciplineCategory::where('is_deleted','no')->where('indicipline_category_status','active')->get();
        $data['ratings'] = StudentIndiciplineRating::where('is_deleted','no')->where('rating_category_status','active')->get();
        $data['staffs'] = StaffDetail::where('is_deleted','no')->where('staff_staus','active')->get();
        return view('admin.advanced-features.student-indicipline.create', $data);
    }

    public function storeCase(Request $request)
    {
        $request->validate([
            'case_date' => 'required',
            'student_id' => 'required',
            'indicipline_category' => 'required',
            'indicipline_rating' => 'required',
            'handled_by' => 'required',
            'action_taken' => 'required',
            'description' => 'required',
        ]);

        try 
        {
            $ins['case_date'] = $request->case_date;
            $ins['student_id'] = $request->student_id;
            $ins['indicipline_category'] = $request->indicipline_category;
            $ins['indicipline_rating'] = $request->indicipline_rating;
            $ins['handled_by'] = $request->handled_by;
            $ins['action_taken'] = $request->action_taken;
            $ins['description'] = $request->description;
            $ins['case_status'] = 'active';
            $ins['is_deleted'] = 'no';
            $ins['created_by'] = Auth::user()->id;
            $ins['updated_by'] = Auth::user()->id;
            StudentIndiciplineCase::create($ins);
            return redirect()->route('admin.indecipline-case-index-page')->with('success', 'Case added successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.indecipline-case-index-page')->with('error', 'Oops something went wrong');
        }
    }
}
