<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DataTables;
use App\Models\NewCurriculamCompetency;
use App\Models\NewCurriculamStudentExamMark;
use App\Models\NewCurriculamExamReportComment;
use App\Models\NewCurriculamStudentExam;
use App\Models\Subject;
use App\Models\SubjectClass;
use App\Models\StudentDetail;
use App\Models\StudentAttendance;
use App\Models\StaffDetail;
use App\Models\StudentExam;
use App\Models\NewCurriculamGameAndSportsComment;
use App\Models\NewCurriculamClubComment;
use App\Models\NewCurriculamProjectComment;
use App\Models\StudentIndiciplineCase;
use App\Models\StudentSickBay;
use App\Models\NewCurriculamValuesExhibitedComment;
use App\Models\Stream;
use Carbon\Carbon;
use Validator;

class NewCurriculamExamController extends Controller
{
    public function index(Request $request){
        if($_GET && count($_GET) > 0){
            $whr_exam_set['class_id'] = $_GET['class_id'];
            $whr_exam_set['subject_id'] = $_GET['subject_id'];
            $whr_exam_set['competency_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';
            $data['get_exam_sets'] = NewCurriculamCompetency::with(['exam_set'])->where($whr_exam_set)->get();
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            if($_GET['stream_id'] !== 'all_streams'){
                $whr_exam_stud['stram_id'] = $_GET['stream_id'];    
            }
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $data['get_exam_students'] = StudentDetail::with(['new_curriculam_exam_marks'])->where($whr_exam_stud)->get();
            //dd($data['get_exam_students']);
            $data['get_exam_subject'] = Subject::where('id', $_GET['subject_id'])->first();
            $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
        }
        $data['title'] = 'Add Exam Marks';
        $data['staff_lists'] = StaffDetail::where('is_deleted', 'no')->where('staff_staus', 'active')->get();
        return view('admin.new-curriculam.exams.to_marks',$data);
    }

    public function addExamMarks(Request $request)
    {
        $request->validate([
            'general_skills.*' => 'required',
            'general_remarks.*' => 'required',
            'exam_score.*' => 'required',
        ]);

        try 
        {
            $student_marks = $request->general_skills;
            $exam_scores = $request->exam_score;
            $general_remarks = $request->general_remarks;

            $whr_exam['class_id'] = $request->class_id;
            $whr_exam['staff_id'] = $request->staff_id;
            $whr_exam['stream_id'] = $request->stream_id;
            $whr_exam['subject_id'] = $request->subject_id;
            $whr_exam['paper_id'] = $request->paper_id;
            $whr_exam['term_id'] = $request->term_id;
            $whr_exam['year'] = $request->year;

            $ins_exam['class_id'] = $request->class_id;
            $ins_exam['staff_id'] = $request->staff_id;
            $ins_exam['stream_id'] = $request->stream_id;
            $ins_exam['subject_id'] = $request->subject_id;
            $ins_exam['paper_id'] = $request->paper_id;
            $ins_exam['term_id'] = $request->term_id;
            $ins_exam['year'] = $request->year;
            $ins_exam['total_students'] = count($student_marks);
            $ins_exam['exam_status'] = 'active';
            $ins_exam['is_deleted'] = 'no';
            $ins_exam['created_by'] = Auth::user()->id;
            $ins_exam['updated_by'] = Auth::user()->id;

            $exam = NewCurriculamStudentExam::updateOrCreate($whr_exam,$ins_exam);
            
            foreach($student_marks as $student_ids => $student_mark){
                $whr_exam_stud['student_id'] = $student_ids;
                $whr_exam_stud['staff_id'] = $request->staff_id;
                $whr_exam_stud['subject_id'] = $request->subject_id;
                $whr_exam_stud['class_id'] = $request->class_id;
                $whr_exam_stud['paper_id'] = $request->paper_id;
                
                $ins_or_upd_exam_stud['general_skills'] = $student_mark;
                $ins_or_upd_exam_stud['general_remarks'] = $general_remarks[$student_ids];
                $ins_or_upd_exam_stud['exam_score'] = json_encode($exam_scores[$student_ids]);
                $ins_or_upd_exam_stud['student_id'] = $student_ids;
                $ins_or_upd_exam_stud['staff_id'] = $request->staff_id;
                $ins_or_upd_exam_stud['exam_id'] = $exam->id;
                $ins_or_upd_exam_stud['subject_id'] = $request->subject_id;
                $ins_or_upd_exam_stud['class_id'] = $request->class_id;
                $ins_or_upd_exam_stud['paper_id'] = $request->paper_id;
                $ins_or_upd_exam_stud['marks_taken'] = $student_mark;
                $ins_or_upd_exam_stud['exam_marks_status'] = 'active';
                $ins_or_upd_exam_stud['is_deleted'] = 'no';
                $ins_or_upd_exam_stud['created_by'] = Auth::user()->id;
                $ins_or_upd_exam_stud['updated_by'] = Auth::user()->id;
                NewCurriculamStudentExamMark::updateOrCreate($whr_exam_stud,$ins_or_upd_exam_stud);
            }
            return redirect()->route('admin.new-curriculam-exams-index-page')->with('success', 'Exam created successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.new-curriculam-exams-index-page')->with('error', 'Oops something went wrong');
        }
    }

    public function examMarksView(Request $request)
    {
        if(count($_GET) > 0){
            $whr_stud_exam['class_id'] = $request->class_id;
            $whr_stud_exam['term_id'] = $request->term_id;
            $whr_stud_exam['year'] = $request->year;
            $whr_stud_exam['stream_id'] = $request->stream_id;

            $whr_exam_set['class_id'] = $_GET['class_id'];
            $whr_exam_set['term_id'] = $_GET['term_id'];
            $whr_exam_set['competency_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';

            $data['get_exam_sets'] = NewCurriculamCompetency::with(['exam_set'])->where($whr_exam_set)->get();

            $student_exams = NewCurriculamStudentExam::with('student_exam_marks')->get();
            if($student_exams->count() > 0){
                $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
                $data['get_exam_subjects'] = Subject::where('subject_study_level',$data['get_exam_class']->class_level)->get();
                $student_marks = array();
                if($student_exams->count() > 0){
                    foreach($student_exams as $student_exam){
                        foreach($student_exam->student_exam_marks as $student_exam_marks){
                            $student_marks[$student_exam_marks->student_id] = json_decode($student_exam_marks->exam_score,TRUE);    
                        }
                    }
                }
                $data['student_marks'] = $student_marks;
            }
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_curriculam','new_curriculam')->get();
        return view('admin.new-curriculam.exams.exam_marks_view',$data);
    }

    public function examMarksViewPrint(Request $request)
    {
        if(count($_GET) > 0){
            $whr_stud_exam['class_id'] = $request->class_id;
            $whr_stud_exam['term_id'] = $request->term_id;
            $whr_stud_exam['year'] = $request->year;
            $whr_stud_exam['stream_id'] = $request->stream_id;

            $whr_exam_set['class_id'] = $_GET['class_id'];
            $whr_exam_set['term_id'] = $_GET['term_id'];
            $whr_exam_set['competency_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';
            if($request->stream_id && !is_null($request->stream_id)){
                $data['stream'] = Stream::where('class_id', $request->stream_id)->first();    
            }
            
            $data['get_exam_sets'] = NewCurriculamCompetency::with(['exam_set'])->where($whr_exam_set)->get();

            $student_exams = NewCurriculamStudentExam::with('student_exam_marks')->get();
            if($student_exams->count() > 0){
                $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
                $data['get_exam_subjects'] = Subject::where('subject_study_level',$data['get_exam_class']->class_level)->get();
                $student_marks = array();
                if($student_exams->count() > 0){
                    foreach($student_exams as $student_exam){
                        foreach($student_exam->student_exam_marks as $student_exam_marks){
                            $student_marks[$student_exam_marks->student_id] = json_decode($student_exam_marks->exam_score,TRUE);    
                        }
                    }
                }
                $data['student_marks'] = $student_marks;
            }
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_curriculam','new_curriculam')->get();
        return view('admin.new-curriculam.exams.exam_marks_view_print',$data);
    }

    public function reportComments(Request $request)
    {
        if(count($_GET) > 0){
            $whr_stu['class_id'] = $request->class_id;
            $whr_stu['term'] = $request->term_id;
            $whr_stu['student_staus'] = 'active';
            $whr_stu['is_deleted'] = 'no';
            $data['students'] = StudentDetail::where($whr_stu)->get();

            $data['stream'] = Stream::where('class_id', $_GET['class_id'])->first();

            $stu_commen_whr['class_id'] = $request->class_id;
            $stu_commen_whr['term_id'] = $request->term_id;
            $stu_commen_whr['year'] = $request->year;

            $student_report_comments_lists = NewCurriculamExamReportComment::where($stu_commen_whr);
            $student_report_comments = array();
            if($student_report_comments_lists->count() > 0){
                foreach ($student_report_comments_lists->get() as $value) {
                    $student_report_comments[$value->student_id]['class_teacher_comments'] = $value->class_teacher_comments;
                    $student_report_comments[$value->student_id]['hm_comments'] = $value->hm_comments;
                }
            }
            $data['student_report_comments'] = $student_report_comments;
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_curriculam','new_curriculam')->get();
        return view('admin.new-curriculam.exams.report_comments',$data);
    }

    public function reportCommentsUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required',
            'class_id' => 'required',
            'year' => 'required',
            'term_id' => 'required',
            'comment_type' => 'required',
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
            $whrClsMrk['student_id'] = $request->student_id;
            $whrClsMrk['class_id'] = $request->class_id;
            $whrClsMrk['year'] = $request->year;
            $whrClsMrk['term_id'] = $request->term_id;
            
            $updateClassMark = NewCurriculamExamReportComment::updateOrCreate($whrClsMrk,[
                'student_id' => $request->student_id,
                'class_id' => $request->class_id,
                'year' => $request->year,
                'term_id' => $request->term_id,
                $request->comment_type => $request->comments,
                'exam_report_status' => 'active',
                'is_deleted' => 'no',
                'created_by' => Auth::user()->id,
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

    public function gamesComments(Request $request)
    {
        if(count($_GET) > 0){
            $whr_stu['class_id'] = $request->class_id;
            $whr_stu['term'] = $request->term_id;
            $whr_stu['student_staus'] = 'active';
            $whr_stu['is_deleted'] = 'no';
            $data['students'] = StudentDetail::where($whr_stu)->get();

            $data['stream'] = Stream::where('class_id', $_GET['class_id'])->first();

            $stu_commen_whr['class_id'] = $request->class_id;
            $stu_commen_whr['term_id'] = $request->term_id;
            $stu_commen_whr['year'] = $request->year;

            $student_report_comments_lists = NewCurriculamGameAndSportsComment::where($stu_commen_whr);
            $student_report_comments = array();
            if($student_report_comments_lists->count() > 0){
                foreach ($student_report_comments_lists->get() as $value) {
                    $student_report_comments[$value->student_id]['comments'] = $value->comments;
                }
            }
            $data['student_report_comments'] = $student_report_comments;
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_curriculam','new_curriculam')->get();
        return view('admin.new-curriculam.exams.games_comments',$data);
    }  

    public function gamesCommentsUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required',
            'class_id' => 'required',
            'year' => 'required',
            'term_id' => 'required',
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
            $whrClsMrk['student_id'] = $request->student_id;
            $whrClsMrk['class_id'] = $request->class_id;
            $whrClsMrk['year'] = $request->year;
            $whrClsMrk['term_id'] = $request->term_id;
            
            $updateClassMark = NewCurriculamGameAndSportsComment::updateOrCreate($whrClsMrk,[
                'student_id' => $request->student_id,
                'class_id' => $request->class_id,
                'year' => $request->year,
                'term_id' => $request->term_id,
                'comments' => $request->comments,
                'exam_report_status' => 'active',
                'is_deleted' => 'no',
                'created_by' => Auth::user()->id,
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

    public function clubsComments(Request $request)
    {
        if(count($_GET) > 0){
            $whr_stu['class_id'] = $request->class_id;
            $whr_stu['term'] = $request->term_id;
            $whr_stu['student_staus'] = 'active';
            $whr_stu['is_deleted'] = 'no';
            $data['students'] = StudentDetail::where($whr_stu)->get();

            $data['stream'] = Stream::where('class_id', $_GET['class_id'])->first();

            $stu_commen_whr['class_id'] = $request->class_id;
            $stu_commen_whr['term_id'] = $request->term_id;
            $stu_commen_whr['year'] = $request->year;

            $student_report_comments_lists = NewCurriculamClubComment::where($stu_commen_whr);
            $student_report_comments = array();
            if($student_report_comments_lists->count() > 0){
                foreach ($student_report_comments_lists->get() as $value) {
                    $student_report_comments[$value->student_id]['comments'] = $value->comments;
                }
            }
            $data['student_report_comments'] = $student_report_comments;
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_curriculam','new_curriculam')->get();
        return view('admin.new-curriculam.exams.club_comments',$data);
    }

    public function clubsCommentsUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required',
            'class_id' => 'required',
            'year' => 'required',
            'term_id' => 'required',
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
            $whrClsMrk['student_id'] = $request->student_id;
            $whrClsMrk['class_id'] = $request->class_id;
            $whrClsMrk['year'] = $request->year;
            $whrClsMrk['term_id'] = $request->term_id;
            
            $updateClassMark = NewCurriculamClubComment::updateOrCreate($whrClsMrk,[
                'student_id' => $request->student_id,
                'class_id' => $request->class_id,
                'year' => $request->year,
                'term_id' => $request->term_id,
                'comments' => $request->comments,
                'exam_report_status' => 'active',
                'is_deleted' => 'no',
                'created_by' => Auth::user()->id,
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

    public function projectComments(Request $request)
    {
        if(count($_GET) > 0){
            $whr_stu['class_id'] = $request->class_id;
            $whr_stu['term'] = $request->term_id;
            $whr_stu['student_staus'] = 'active';
            $whr_stu['is_deleted'] = 'no';
            $data['students'] = StudentDetail::where($whr_stu)->get();

            $data['stream'] = Stream::where('class_id', $_GET['class_id'])->first();

            $stu_commen_whr['class_id'] = $request->class_id;
            $stu_commen_whr['term_id'] = $request->term_id;
            $stu_commen_whr['year'] = $request->year;

            $student_report_comments_lists = NewCurriculamProjectComment::where($stu_commen_whr);
            $student_report_comments = array();
            if($student_report_comments_lists->count() > 0){
                foreach ($student_report_comments_lists->get() as $value) {
                    $student_report_comments[$value->student_id]['project_remarks'] = $value->project_remarks;
                    $student_report_comments[$value->student_id]['project_title'] = $value->project_title;
                }
            }
            $data['student_report_comments'] = $student_report_comments;
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_curriculam','new_curriculam')->get();
        return view('admin.new-curriculam.exams.project_comments',$data);
    }

    public function projectCommentsUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required',
            'class_id' => 'required',
            'year' => 'required',
            'term_id' => 'required',
            'comment_type' => 'required',
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
            $whrClsMrk['student_id'] = $request->student_id;
            $whrClsMrk['class_id'] = $request->class_id;
            $whrClsMrk['year'] = $request->year;
            $whrClsMrk['term_id'] = $request->term_id;
            
            $updateClassMark = NewCurriculamProjectComment::updateOrCreate($whrClsMrk,[
                'student_id' => $request->student_id,
                'class_id' => $request->class_id,
                'year' => $request->year,
                'term_id' => $request->term_id,
                $request->comment_type => $request->comments,
                'exam_report_status' => 'active',
                'is_deleted' => 'no',
                'created_by' => Auth::user()->id,
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

    public function valuesExhibitedComments(Request $request)
    {
        if(count($_GET) > 0){
            $whr_stu['class_id'] = $request->class_id;
            $whr_stu['term'] = $request->term_id;
            $whr_stu['student_staus'] = 'active';
            $whr_stu['is_deleted'] = 'no';
            $data['students'] = StudentDetail::where($whr_stu)->get();

            $data['stream'] = Stream::where('class_id', $_GET['class_id'])->first();

            $stu_commen_whr['class_id'] = $request->class_id;
            $stu_commen_whr['term_id'] = $request->term_id;
            $stu_commen_whr['year'] = $request->year;

            $student_report_comments_lists = NewCurriculamValuesExhibitedComment::where($stu_commen_whr);
            $student_report_comments = array();
            if($student_report_comments_lists->count() > 0){
                foreach ($student_report_comments_lists->get() as $value) {
                    $student_report_comments[$value->student_id]['comments'] = $value->comments;
                }
            }
            $data['student_report_comments'] = $student_report_comments;
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_curriculam','new_curriculam')->get();
        return view('admin.new-curriculam.exams.values_exhibited',$data);
    }

    public function valuesExhibitedCommentsUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required',
            'class_id' => 'required',
            'year' => 'required',
            'term_id' => 'required',
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
            $whrClsMrk['student_id'] = $request->student_id;
            $whrClsMrk['class_id'] = $request->class_id;
            $whrClsMrk['year'] = $request->year;
            $whrClsMrk['term_id'] = $request->term_id;
            
            $updateClassMark = NewCurriculamValuesExhibitedComment::updateOrCreate($whrClsMrk,[
                'student_id' => $request->student_id,
                'class_id' => $request->class_id,
                'year' => $request->year,
                'term_id' => $request->term_id,
                'comments' => $request->comments,
                'exam_report_status' => 'active',
                'is_deleted' => 'no',
                'created_by' => Auth::user()->id,
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

    public function reportCards(Request $request)
    {
        if(count($_GET) > 0){

            $whr_exam_set['class_id'] = $request['class_id'];
            $whr_exam_set['competency_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';
            $data['get_exam_sets'] = NewCurriculamCompetency::with(['exam_set'])->where($whr_exam_set)->get();

            $stu_games_commen_whr['class_id'] = $request->class_id;
            $stu_games_commen_whr['year'] = $request->year;

            $student_games_comments_lists = NewCurriculamGameAndSportsComment::where($stu_games_commen_whr);
            $student_games_comments = array();
            if($student_games_comments_lists->count() > 0){
                foreach ($student_games_comments_lists->get() as $value) {
                    $student_games_comments[$value->student_id]['comments'] = $value->comments;
                }
            }
            $data['student_games_comments'] = $student_games_comments;

            $stu_club_commen_whr['class_id'] = $request->class_id;
            $stu_club_commen_whr['year'] = $request->year;

            $student_club_comments_lists = NewCurriculamClubComment::where($stu_club_commen_whr);
            $student_club_comments = array();
            if($student_club_comments_lists->count() > 0){
                foreach ($student_club_comments_lists->get() as $value2) {
                    $student_club_comments[$value2->student_id]['comments'] = $value2->comments;
                }
            }
            $data['student_club_comments'] = $student_club_comments;

            $stu_project_commen_whr['class_id'] = $request->class_id;
            $stu_project_commen_whr['year'] = $request->year;

            $student_project_comments_lists = NewCurriculamProjectComment::where($stu_project_commen_whr);
            $student_project_comments = array();
            if($student_project_comments_lists->count() > 0){
                foreach ($student_project_comments_lists->get() as $value1) {
                    $student_project_comments[$value1->student_id]['project_title'] = $value1->project_title;
                    $student_project_comments[$value1->student_id]['project_remarks'] = $value1->project_remarks;
                }
            }
            $data['student_project_comments'] = $student_project_comments;

            $stu_value_ex_whr['class_id'] = $request->class_id;
            $stu_value_ex_whr['year'] = $request->year;

            $student_value_comments_lists = NewCurriculamValuesExhibitedComment::where($stu_value_ex_whr);
            $student_value_comments = array();
            if($student_value_comments_lists->count() > 0){
                foreach ($student_value_comments_lists->get() as $value3) {
                    $student_value_comments[$value3->student_id]['comments'] = $value3->comments;
                }
            }
            $data['student_value_comments'] = $student_value_comments;

            $whrComments['class_id'] = $request->class_id;
            $whrComments['exam_report_status'] = 'active';
            $whrComments['is_deleted'] = 'no';
            $student_report_comments = array();
            $reportComments = NewCurriculamExamReportComment::where($whrComments)->get();
            if($reportComments->count() > 0){
                foreach($reportComments as $reportComment){
                    $student_report_comments[$reportComment->student_id]['class_teacher_comments'] = $reportComment->class_teacher_comments;
                    $student_report_comments[$reportComment->student_id]['hm_comments'] = $reportComment->hm_comments;
                }
            }
            
            $data['student_report_comments'] = $student_report_comments;
            $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
            
            $data['get_exam_subjects'] = Subject::where('subject_curriculam', 'new_curriculam')->with('subject_compentency')->where('subject_staus','active')->get();

            $whr_exam_stud['class_id'] = $_GET['class_id'];
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $data['get_exam_students'] = StudentDetail::with(['new_curriculam_exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();
            
            $student_attendances = StudentAttendance::all();
            $stud_present_days = array();
            $stud_absent_days = array();
            if($student_attendances->count() > 0){
                foreach($student_attendances as $student_atted){
                    if($student_atted->attendance_status == 'present' || $student_atted->attendance_status == 'half_day'){
                        $stud_present_days[$student_atted->student_id][] = $student_atted->id;    
                    }
                    if($student_atted->attendance_status == 'absent'){
                        $stud_absent_days[$student_atted->student_id][] = $student_atted->id;    
                    }
                }
            }
            $data['stud_absent_days'] = $stud_absent_days;
            $data['stud_present_days'] = $stud_present_days;

            $student_indiciplnine_count = array();
            $student_indiciplines = StudentIndiciplineCase::where('case_status', 'active')->get();

            foreach($student_indiciplines as $student_indicipline){
                $student_indiciplnine_count[$student_indicipline->student_id][] = $student_indicipline->id;
            }
            $data['student_indiciplnine_count'] = $student_indiciplnine_count;

            $student_sickbay_count = array();
            $student_sickbay_lists = StudentSickBay::where('student_sick_status', 'active')->get();

            foreach($student_sickbay_lists as $student_sickbay_list){
                $student_sickbay_count[$student_sickbay_list->student_id][] = $student_sickbay_list->id;
            }
            $data['student_sickbay_count'] = $student_sickbay_count;

        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_curriculam','new_curriculam')->get();
        return view('admin.new-curriculam.exams.report_cards',$data);
    }

    public function reportCardsPrint(Request $request)
    {
        if(count($_GET) > 0){

            $whr_exam_set['class_id'] = $request['class_id'];
            $whr_exam_set['competency_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';
            $data['get_exam_sets'] = NewCurriculamCompetency::with(['exam_set'])->where($whr_exam_set)->get();

            $stu_games_commen_whr['class_id'] = $request->class_id;
            $stu_games_commen_whr['year'] = $request->year;

            $student_games_comments_lists = NewCurriculamGameAndSportsComment::where($stu_games_commen_whr);
            $student_games_comments = array();
            if($student_games_comments_lists->count() > 0){
                foreach ($student_games_comments_lists->get() as $value) {
                    $student_games_comments[$value->student_id]['comments'] = $value->comments;
                }
            }
            $data['student_games_comments'] = $student_games_comments;

            $stu_club_commen_whr['class_id'] = $request->class_id;
            $stu_club_commen_whr['year'] = $request->year;

            $student_club_comments_lists = NewCurriculamClubComment::where($stu_club_commen_whr);
            $student_club_comments = array();
            if($student_club_comments_lists->count() > 0){
                foreach ($student_club_comments_lists->get() as $value2) {
                    $student_club_comments[$value2->student_id]['comments'] = $value2->comments;
                }
            }
            $data['student_club_comments'] = $student_club_comments;

            $stu_project_commen_whr['class_id'] = $request->class_id;
            $stu_project_commen_whr['year'] = $request->year;

            $student_project_comments_lists = NewCurriculamProjectComment::where($stu_project_commen_whr);
            $student_project_comments = array();
            if($student_project_comments_lists->count() > 0){
                foreach ($student_project_comments_lists->get() as $value1) {
                    $student_project_comments[$value1->student_id]['project_title'] = $value1->project_title;
                    $student_project_comments[$value1->student_id]['project_remarks'] = $value1->project_remarks;
                }
            }
            $data['student_project_comments'] = $student_project_comments;

            $stu_value_ex_whr['class_id'] = $request->class_id;
            $stu_value_ex_whr['year'] = $request->year;

            $student_value_comments_lists = NewCurriculamValuesExhibitedComment::where($stu_value_ex_whr);
            $student_value_comments = array();
            if($student_value_comments_lists->count() > 0){
                foreach ($student_value_comments_lists->get() as $value3) {
                    $student_value_comments[$value3->student_id]['comments'] = $value3->comments;
                }
            }
            $data['student_value_comments'] = $student_value_comments;

            $whrComments['class_id'] = $request->class_id;
            $whrComments['exam_report_status'] = 'active';
            $whrComments['is_deleted'] = 'no';
            $student_report_comments = array();
            $reportComments = NewCurriculamExamReportComment::where($whrComments)->get();
            if($reportComments->count() > 0){
                foreach($reportComments as $reportComment){
                    $student_report_comments[$reportComment->student_id]['class_teacher_comments'] = $reportComment->class_teacher_comments;
                    $student_report_comments[$reportComment->student_id]['hm_comments'] = $reportComment->hm_comments;
                }
            }
            
            $data['student_report_comments'] = $student_report_comments;
            $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
            
            $data['get_exam_subjects'] = Subject::where('subject_curriculam', 'new_curriculam')->with('subject_compentency')->where('subject_staus','active')->get();

            $whr_exam_stud['class_id'] = $_GET['class_id'];
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $data['get_exam_students'] = StudentDetail::with(['new_curriculam_exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();
            
            $student_attendances = StudentAttendance::all();
            $stud_present_days = array();
            $stud_absent_days = array();
            if($student_attendances->count() > 0){
                foreach($student_attendances as $student_atted){
                    if($student_atted->attendance_status == 'present' || $student_atted->attendance_status == 'half_day'){
                        $stud_present_days[$student_atted->student_id][] = $student_atted->id;    
                    }
                    if($student_atted->attendance_status == 'absent'){
                        $stud_absent_days[$student_atted->student_id][] = $student_atted->id;    
                    }
                }
            }
            $data['stud_absent_days'] = $stud_absent_days;
            $data['stud_present_days'] = $stud_present_days;

            $student_indiciplnine_count = array();
            $student_indiciplines = StudentIndiciplineCase::where('case_status', 'active')->get();

            foreach($student_indiciplines as $student_indicipline){
                $student_indiciplnine_count[$student_indicipline->student_id][] = $student_indicipline->id;
            }
            $data['student_indiciplnine_count'] = $student_indiciplnine_count;

            $student_sickbay_count = array();
            $student_sickbay_lists = StudentSickBay::where('student_sick_status', 'active')->get();

            foreach($student_sickbay_lists as $student_sickbay_list){
                $student_sickbay_count[$student_sickbay_list->student_id][] = $student_sickbay_list->id;
            }
            $data['student_sickbay_count'] = $student_sickbay_count;

        }
        //$data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_curriculam','new_curriculam')->where('class_status','active')->get();
        return view('admin.new-curriculam.exams.report_cards_print',$data);
    }
}
