<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\SubjectClass;
use App\Models\StaffDetail;
use App\Models\StudentDetail;
use App\Models\Stream;
use App\Models\SubjectPaper;
use App\Models\StaffLoad;
use App\Models\Subject;
use App\Models\SubjectGrading;
use App\Models\ClassSetMark;
use App\Models\ExamSet;
use App\Models\StudentExamMark;
use App\Models\StudentExamReportComment;
use App\Models\StudentExam;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Validator;

class ExamController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:staff.exams-add-marks-page|staff.exams-add-exam-marks|staff.exams-report-comments-page|staff.exams-report-cards-page|staff.exams-report-cards-print-page|staff.exams-report-comments-update']);
    }

    public function index(Request $request){
        if($_GET && count($_GET) > 0){
            $whr_exam_set['class_id'] = $_GET['class_id'];
            $whr_exam_set['mark_set_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';
            $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
            $data['get_exam_sets'] = ClassSetMark::with(['exam_set_details'])
                                    ->where($whr_exam_set)
                                    ->whereHas('exam_set_details',function($query) use ($data){
                                        $query->where('set_curriculam', $data['get_exam_class']->class_curriculam);
                                    })
                                    ->get();

            $whr_exam_stud['class_id'] = $_GET['class_id'];
            if(!is_null($_GET['stream_id']) && !empty($_GET['stream_id']) && $_GET['stream_id'] !== 'all_streams'){
                $whr_exam_stud['stram_id'] = $_GET['stream_id'];    
            }
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $data['get_exam_students'] = StudentDetail::with(['exam_marks'])
                                    ->where($whr_exam_stud)
                                    ->get();
            $data['get_exam_subject'] = Subject::where('id', $_GET['subject_id'])->first();
            $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
        }
        $data['title'] = 'Add Exam Marks';
        $data['staff_lists'] = StaffDetail::where('is_deleted', 'no')->where('staff_staus', 'active')->get();
        return view('staff.exams.to_marks',$data);
    }

    public function addExamMarks(Request $request){
        $validator = Validator::make($request->all(), [
            "student_marks"    => "required|array",
            "student_marks.*"  => "required",
        ],['student_marks' => 'Please enter the student marks']);

   
        if($validator->fails()){
            return redirect()->route('staff.exams-add-marks-page',[
                    'staff_id' => $request->staff_id,
                    'class_id' => $request->class_id,
                    'stream_id' => $request->stream_id,
                    'subject_id' => $request->subject_id,
                    'paper_id' => $request->paper_id,
                    'term_id' => $request->term_id,
                    'year' => $request->year,
                ])
                ->withErrors($validator->errors());
        }

        try 
        {
            $student_marks = $request->student_marks;
            $student_average_total = $request->student_average_marks;
            $student_total_marks = $request->student_total_marks;
            
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

            $exam = StudentExam::updateOrCreate($whr_exam,$ins_exam);
            foreach($student_marks as $student_ids => $student_mark){
                foreach ($student_mark as $exam_set_id => $mark) {
                    $whr_exam_stud['student_id'] = $student_ids;
                    //$whr_exam_stud['exam_id'] = $exam->id;
                    $whr_exam_stud['exam_set_id'] = $exam_set_id;
                    $whr_exam_stud['subject_id'] = $request->subject_id;
                    $whr_exam_stud['class_id'] = $request->class_id;
                    if(isset($request->paper_id) && is_null($request->paper_id)){
                        $whr_exam_stud['paper_id'] = $request->paper_id;    
                    }
                    if(isset($request->stream_id) && $request->stream_id !== 'all_streams'){
                        $ins_or_upd_exam_stud['stream_id'] = $request->stream_id;   
                    }
                    $whr_exam_stud['staff_id'] = $request->staff_id;
                    $whr_exam_stud['year'] = $request->year;
                    $whr_exam_stud['term'] = $request->term_id;
                    $ins_or_upd_exam_stud['student_id'] = $student_ids;
                    $ins_or_upd_exam_stud['staff_id'] = $request->staff_id;
                    $ins_or_upd_exam_stud['exam_id'] = $exam->id;
                    $ins_or_upd_exam_stud['subject_id'] = $request->subject_id;
                    $ins_or_upd_exam_stud['class_id'] = $request->class_id;
                    $ins_or_upd_exam_stud['paper_id'] = $request->paper_id;
                    $ins_or_upd_exam_stud['exam_set_id'] = $exam_set_id;
                    $ins_or_upd_exam_stud['term'] = $request->term_id;
                    $ins_or_upd_exam_stud['year'] = $request->year;
                    $ins_or_upd_exam_stud['marks_taken'] = $mark;
                    $ins_or_upd_exam_stud['average_total'] = isset($student_average_total[$student_ids]) && !empty($student_average_total[$student_ids]) ? $student_average_total[$student_ids] : 0;
                    $ins_or_upd_exam_stud['total_marks'] = isset($student_total_marks[$student_ids]) && !empty($student_total_marks[$student_ids]) ? $student_total_marks[$student_ids] : 0;
                    $ins_or_upd_exam_stud['exam_marks_status'] = 'active';
                    $ins_or_upd_exam_stud['is_deleted'] = 'no';
                    $ins_or_upd_exam_stud['created_by'] = Auth::user()->id;
                    $ins_or_upd_exam_stud['updated_by'] = Auth::user()->id;
                    StudentExamMark::updateOrCreate($whr_exam_stud,$ins_or_upd_exam_stud);
                }
            }
            return redirect()->route('staff.exams-add-marks-page')->with('success', 'Exam created successfully');
        } catch (Exception $e) {
            return redirect()->route('staff.exams-add-marks-page')->with('error', 'Oops something went wrong');
        }
    }

    public function examMarksView(Request $request)
    {
        if(count($_GET) > 0){
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            if(!is_null($_GET['stream_id']) && !empty($_GET['stream_id']) && $_GET['stream_id'] !== 'all_streams'){
                $whr_exam_stud['stram_id'] = $_GET['stream_id'];    
            }
            $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $data['get_exam_students'] = StudentDetail::with(['exam_marks'])
                                    ->where($whr_exam_stud)
                                    ->get();
            $data['get_exam_subjects'] = Subject::where('subject_curriculam', $data['get_exam_class']->class_curriculam)->get();
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->get();
        return view('staff.exams.exam_marks_view',$data);
    }

    public function subjectAnalysis(Request $request)
    {
        if(count($_GET) > 0){
            $whr_stud_exam['class_id'] = $request->class_id;
            $whr_stud_exam['term_id'] = $request->term_id;
            $whr_stud_exam['year'] = $request->year;
            if($_GET['stream_id'] !== 'all_streams'){
                $whr_stud_exam['stream_id'] = $request->stream_id;    
            }
            $whr_exam_stud['subject_id'] = $request->subject_id;
            $student_exams = StudentExam::with('student_exam_marks')->get();
            if($student_exams->count() > 0){
                $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
                $data['get_exam_subject'] = Subject::where('id', $_GET['class_id'])->first();
                $student_marks = array();
                $student_exam_sets = array();
                if($student_exams->count() > 0){
                    foreach($student_exams as $student_exam){
                        foreach($student_exam->student_exam_marks as $student_exam_marks){
                            $student_marks[$student_exam_marks->student_id][$student_exam_marks->exam_set_id] = $student_exam_marks->marks_taken;
                            $student_exam_sets[$student_exam_marks->exam_set_id] = $student_exam_marks->exam_set_id;
                        }
                    }
                }
                $data['get_exam_sets'] = ClassSetMark::with('exam_set_details')->whereIn('id',$student_exam_sets)->get();
                $data['student_exam_sets'] = $student_exam_sets;
                $data['student_marks'] = $student_marks;
            }
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_curriculam','new_curriculam')->where('class_status','active')->get();
        return view('staff.exams.subject_analysis',$data);
    }

    public function reportComments(Request $request)
    {
        if(count($_GET) > 0){
            $whr_stud_exam['class_id'] = $request->class_id;
            $whr_stud_exam['term_id'] = $request->term_id;
            $whr_stud_exam['year'] = $request->year;
            $whr_exam_stud['subject_id'] = $request->subject_id;
            $student_exams = StudentExam::with('student_exam_marks')->get();
            if($student_exams->count() > 0){
                $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
                $data['get_exam_subjects'] = Subject::where('subject_study_level', $data['get_exam_class']->class_level)->get();
                $student_marks = array();
                $student_exam_sets = array();
                if($student_exams->count() > 0){
                    foreach($student_exams as $student_exam){
                        foreach($student_exam->student_exam_marks as $student_exam_marks){
                            $student_marks[$student_exam_marks->student_id][$student_exam_marks->exam_set_id] = $student_exam_marks->marks_taken;
                            $student_exam_sets[$student_exam_marks->exam_set_id] = $student_exam_marks->exam_set_id;
                        }
                    }
                }
                $whrComments['class_id'] = $request->class_id;
                $whrComments['term_id'] = $request->term_id;
                $whrComments['year'] = $request->year;
                $whrComments['exam_report_status'] = 'active';
                $whrComments['is_deleted'] = 'no';
                $student_report_comments = array();
                $reportComments = StudentExamReportComment::where($whrComments)->get();
                if($reportComments->count() > 0){
                    foreach($reportComments as $reportComment){
                        $student_report_comments[$reportComment->student_id]['class_teacher_comments'] = $reportComment->class_teacher_comments;
                        $student_report_comments[$reportComment->student_id]['conduct_comments'] = $reportComment->conduct_comments;
                        $student_report_comments[$reportComment->student_id]['hm_comments'] = $reportComment->hm_comments;
                    }
                }
                $data['student_report_comments'] = $student_report_comments;
                $data['get_exam_sets'] = ClassSetMark::with('exam_set_details')->whereIn('id',$student_exam_sets)->get();
                $data['student_exam_sets'] = $student_exam_sets;
                $data['student_marks'] = $student_marks;
            }
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->get();
        return view('staff.exams.report_comments',$data);
    }

    public function reportCards(Request $request)
    {
        if(count($_GET) > 0){
            $whrComments['class_id'] = $request->class_id;
            $whrComments['term_id'] = $request->term_id;
            $whrComments['year'] = $request->year;
            $whrComments['exam_report_status'] = 'active';
            $whrComments['is_deleted'] = 'no';
            $student_report_comments = array();
            $reportComments = StudentExamReportComment::where($whrComments)->get();
            if($reportComments->count() > 0){
                foreach($reportComments as $reportComment){
                    $student_report_comments[$reportComment->student_id]['class_teacher_comments'] = $reportComment->class_teacher_comments;
                    $student_report_comments[$reportComment->student_id]['conduct_comments'] = $reportComment->conduct_comments;
                    $student_report_comments[$reportComment->student_id]['hm_comments'] = $reportComment->hm_comments;
                }
            }
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $data['get_exam_students'] = StudentDetail::with(['exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();
            $whr_exam_set['class_id'] = $_GET['class_id'];
            $whr_exam_set['mark_set_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';
            $data['get_exam_sets'] = ClassSetMark::with(['exam_set_details'])->where($whr_exam_set)->whereHas('exam_set_details',function($query) use ($data){ $query->where('set_curriculam', $data['get_exam_class']->class_curriculam); })->get();
            //dd($data['get_exam_sets']);
            $data['get_exam_subjects'] = Subject::where('subject_curriculam', $data['get_exam_class']->class_curriculam)->get();
            $data['get_exam_gradings'] = SubjectGrading::where('study_level',$data['get_exam_class']->class_level)->where('grade_status','active')->where('is_deleted','no')->orderBy('grade','DESC')->get();
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->get();
        return view('staff.exams.report_cards',$data);
    }

    public function reportCardsPrint(Request $request)
    {
        if(count($_GET) > 0){
            $whrComments['class_id'] = $request->class_id;
            $whrComments['term_id'] = $request->term_id;
            $whrComments['year'] = $request->year;
            $whrComments['exam_report_status'] = 'active';
            $whrComments['is_deleted'] = 'no';
            $student_report_comments = array();
            $reportComments = StudentExamReportComment::where($whrComments)->get();
            if($reportComments->count() > 0){
                foreach($reportComments as $reportComment){
                    $student_report_comments[$reportComment->student_id]['class_teacher_comments'] = $reportComment->class_teacher_comments;
                    $student_report_comments[$reportComment->student_id]['conduct_comments'] = $reportComment->conduct_comments;
                    $student_report_comments[$reportComment->student_id]['hm_comments'] = $reportComment->hm_comments;
                }
            }
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            $data['get_exam_class'] = SubjectClass::where('id', $_GET['class_id'])->first();
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $data['get_exam_students'] = StudentDetail::with(['exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();
            $whr_exam_set['class_id'] = $_GET['class_id'];
            $whr_exam_set['mark_set_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';
            $data['get_exam_sets'] = ClassSetMark::with(['exam_set_details'])->where($whr_exam_set)->whereHas('exam_set_details',function($query) use ($data){ $query->where('set_curriculam', $data['get_exam_class']->class_curriculam); })->get();
            $data['get_exam_subjects'] = Subject::where('subject_curriculam', $data['get_exam_class']->class_curriculam)->get();
            $data['get_exam_gradings'] = SubjectGrading::where('study_level',$data['get_exam_class']->class_level)->where('grade_status','active')->where('is_deleted','no')->orderBy('grade','DESC')->get();
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->get();
        return view('staff.exams.report_cards_print',$data);
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
            
            $updateClassMark = StudentExamReportComment::updateOrCreate($whrClsMrk,[
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

    public function getStudentsByClassId(Request $request)
    {
        $where_stud['class_id'] = $request->class_id;
        $where_stud['student_staus'] = 'active';
        $where_stud['is_deleted'] = 'no';
        $students = StudentDetail::where($where_stud)->get();
        $return = '<option value="">Select</option>';
        if($students->count() > 0){
            foreach ($students as $value) {
                $return.='<option value="'.$value->id.'">'.$value->student_name.'</option>';
            }
        }
        return $return;
    }

    public function getSubjectAnalysisSubjects(Request $request)
    {
        $class_details = SubjectClass::where('id', $request->class_id)->first();
        $subject_lists = Subject::where('subject_study_level',$class_details->class_level)->where('subject_staus', 'active')->where('is_deleted', 'no')->get();
        $subject_options = '<option value="">Select</option>';
        if($subject_lists->count() > 0){
            foreach ($subject_lists as $value) {
                $subject_options.='<option value="'.$value->id.'">'.$value->subject_name.'</option>';
            }
        }
        $stream_lists = Stream::where('class_id', $request->class_id)->where('is_deleted','no')->where('stream_status','active')->get();
        $stream_options = '<option value="">Select</option><option value="all_streams">All Streams</option>';
        if($stream_lists->count() > 0){
            foreach($stream_lists as $stream_list){
                $stream_options.='<option value="'.$stream_list->id.'">'.$stream_list->stream_name.'</option>';
            }
        }
        return response()->json([
            'subject_options' => $subject_options,
            'stream_options' => $stream_options,
        ],200);
    }

    public function getStaffClass(Request $request)
    {
        $where_load['staff_id'] = $request->staff_id;
        $where_load['load_status'] = 'active';
        $where_load['is_deleted'] = 'no';
        $loads = StaffLoad::where($where_load)->get();
        $class_ids = array();
        if($loads->count() > 0){
            foreach($loads as $load){
                $class_ids[$load->class_id] = $load->class_id;    
            }
        }
        $class_lists = SubjectClass::whereIn('id',$class_ids)->where('class_status', 'active')->where('is_deleted', 'no')->get();
        $return = '<option value="">Select</option>';
        if($class_lists->count() > 0){
            foreach ($class_lists as $value) {
                $return.='<option value="'.$value->id.'">'.$value->class_name.'</option>';
            }
        }
        return $return;
    }

    public function getStaffSubject(Request $request)
    {
        $where_load['staff_id'] = $request->staff_id;
        $where_load['class_id'] = $request->class_id;
        $where_load['load_status'] = 'active';
        $where_load['is_deleted'] = 'no';
        $loads = StaffLoad::where($where_load)->get();
        
        $class_details = SubjectClass::where('id',$request->class_id)->where('class_status', 'active')->where('is_deleted', 'no')->first();
        $subject_ids = array();
        if($loads->count() > 0){
            foreach($loads as $load){  
                $subject_ids[$load->subject_id] = $load->subject_id;    
            }
        }
        $subject_lists = Subject::whereIn('id',$subject_ids)->where('subject_staus', 'active')->where('is_deleted', 'no')->where('subject_curriculam', $class_details->class_curriculam)->get();

        
        $subject_options = '<option value="">Select</option>';
        if($subject_lists->count() > 0){
            foreach ($subject_lists as $value) {
                $subject_options.='<option value="'.$value->id.'">'.$value->subject_name.'</option>';
            }
        }
        $stream_lists = Stream::where('class_id', $request->class_id)->where('is_deleted','no')->where('stream_status','active')->get();
        $stream_options = '<option value="">Select</option><option value="all_streams">All Streams</option>';
        if($stream_lists->count() > 0){
            foreach($stream_lists as $stream_list){
                $stream_options.='<option value="'.$stream_list->id.'">'.$stream_list->stream_name.'</option>';
            }
        }
        return response()->json([
            'subject_options' => $subject_options,
            'stream_options' => $stream_options,
        ],200);
    }

    public function getStaffSubjectPapers(Request $request)
    {
        $where_paper['subject_id'] = $request->subject_id;
        $where_paper['paper_staus'] = 'active';
        $where_paper['is_deleted'] = 'no';
        $paper_lists = SubjectPaper::where($where_paper)->get();
        $return = '<option value="">Select</option>';
        if($paper_lists->count() > 0){
            foreach ($paper_lists as $value) {
                $return.='<option value="'.$value->id.'">Paper '.$value->paper_name.'</option>';
            }
        }
        return response()->json([
            'count' => $paper_lists->count(),
            'options' => $return,
        ],200);
    }
}
