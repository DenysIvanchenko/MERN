<?php

namespace App\Http\Controllers\Admin;

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
use App\Models\ClassNextTermFee;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Validator;

class SystemReportController extends Controller
{
    public function studentReports(Request $request)
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
            $data['get_exam_sets'] = ClassSetMark::with(['exam_set_details'])->where($whr_exam_set)->whereHas('exam_set_details',function($query) use ($data){ $query->where('set_curriculam', $data['get_exam_class']->class_curriculam);$query->where('set_status', 'active');
                                        $query->where('is_deleted', 'no'); })->get();
            $data['next_term_fee'] = ClassNextTermFee::where('class_id', $request->class_id)->first();
            $data['get_exam_subjects'] = Subject::with('subject_papers')->where('is_deleted','no')->where('subject_staus','active')->where('subject_curriculam', $data['get_exam_class']->class_curriculam)->get();
            $data['get_exam_gradings'] = SubjectGrading::where('study_level',$data['get_exam_class']->class_level)->where('grade_status','active')->where('is_deleted','no')->orderBy('grade','ASC')->get();
            $data['student_report_comments'] = $student_report_comments;
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->get();
        return view('admin.system-reports.student_reports',$data);
    }

    public function alevelPapersReports(Request $request)
    {
        if(count($_GET) > 0){
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $get_exam_students = StudentDetail::with(['exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();

            $stud_avg_marks = array();
            $data['get_exam_class'] = SubjectClass::where('id', $request['class_id'])->first();

            if(isset($get_exam_students) && $get_exam_students->count() > 0)
            {
                foreach($get_exam_students as $get_exam_student)
                {
                    if(!is_null($get_exam_student->exam_marks)){
                          foreach ($get_exam_student->exam_marks->toArray() as $value) 
                          {
                                if(!is_null(SubjectGrading::getGradings($value['average_total'],$data['get_exam_class']->class_level)))
                                {
                                    $stud_avg_marks[$value['subject_id']][$value['paper_id']][SubjectGrading::getGradings($value['average_total'],$data['get_exam_class']->class_level)->grade][] = $value['student_id'];    
                                }
                                
                           }
                    }
                }
            }
            $data['stud_avg_marks'] = $stud_avg_marks;
            //dd(($data['stud_avg_marks']));
            $whr_exam_set['class_id'] = $_GET['class_id'];
            $whr_exam_set['mark_set_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';
            $data['get_papers'] = SubjectPaper::with(['subject'])->where('paper_staus','active')->get();
            $data['get_exam_gradings'] = SubjectGrading::where('study_level',$data['get_exam_class']->class_level)->where('grade_status','active')->where('is_deleted','no')->orderBy('grade','DESC')->get();
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_level', 'advanced_level')->get();
        return view('admin.system-reports.a_level_papers_reports',$data);
    }

    public function alevelSubjectAnalysisReports(Request $request)
    {
        if(count($_GET) > 0){
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $get_exam_students = $data['get_exam_students'] = StudentDetail::with(['exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();

            $stud_avg_marks = array();
            $data['get_exam_class'] = SubjectClass::where('id', $request['class_id'])->first();

            if(isset($get_exam_students) && $get_exam_students->count() > 0)
            {
                foreach($get_exam_students as $get_exam_student)
                {
                    if(!is_null($get_exam_student->exam_marks)){
                          foreach ($get_exam_student->exam_marks->toArray() as $value) 
                          {
                                if(!is_null(SubjectGrading::getGradings($value['average_total'],$data['get_exam_class']->class_level)))
                                {
                                    $stud_avg_marks[$value['subject_id']][$value['paper_id']][$value['student_id']] = SubjectGrading::getGradings($value['average_total'],$data['get_exam_class']->class_level)->grade;    
                                }
                                
                           }
                    }
                }
            }
            $data['stud_avg_marks'] = $stud_avg_marks;
            $whr_exam_set['class_id'] = $_GET['class_id'];
            $whr_exam_set['mark_set_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';
            $data['get_exam_subjects'] = Subject::with(['subject_papers'])->where('subject_study_level','advanced_level')->where('subject_staus','active')->get();
            
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_level', 'advanced_level')->get();
        $data['title'] = 'Subject Analysis Report';
        return view('admin.system-reports.a_level_subject_analysis_reports',$data);
    }

    public function classPerformanceReports(Request $request)
    {
        if(count($_GET) > 0){
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $data['get_exam_students'] = StudentDetail::with(['exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();
            $data['get_exam_class'] = SubjectClass::where('id', $request['class_id'])->first();
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_level', 'advanced_level')->get();
        $data['exam_sets'] = ExamSet::where('is_deleted','no')->where('set_curriculam','old_curriculam')->where('set_status','active')->get();
        $data['title'] = 'Class Performance Report';
        return view('admin.system-reports.class_performance_reports',$data);
    } 

    public function alevelPerformanceReports(Request $request)
    {
        if(count($_GET) > 0){
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $get_exam_students = $data['get_exam_students'] = StudentDetail::with(['exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();

            $stud_avg_marks = array();
            $data['get_exam_class'] = SubjectClass::where('id', $request['class_id'])->first();

            if(isset($get_exam_students) && $get_exam_students->count() > 0)
            {
                foreach($get_exam_students as $get_exam_student)
                {
                    if(!is_null($get_exam_student->exam_marks)){
                          foreach ($get_exam_student->exam_marks->toArray() as $value) 
                          {
                                $stud_avg_marks[$value['subject_id']][SubjectGrading::get_a_level_gradings($value['marks_taken'])][] = $value['student_id'];
                                
                           }
                    }
                }
            }
            $data['stud_avg_marks'] = $stud_avg_marks;
            $data['get_exam_subjects'] = Subject::where('subject_study_level','advanced_level')->where('subject_staus','active')->get();
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_level', 'advanced_level')->get();
        $data['title'] = 'A level performance report';
        return view('admin.system-reports.a_level_performance_reports',$data);
    }

    public function divisionByTableReports(Request $request)
    {
        $whr_exam_stud['student_staus'] = 'active';
        $whr_exam_stud['is_deleted'] = 'no';
        $get_exam_students = $data['get_exam_students'] = StudentDetail::with(['exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();

        $stud_avg_marks = array();
       
        if(isset($get_exam_students) && $get_exam_students->count() > 0)
        {
            foreach($get_exam_students as $get_exam_student)
            {
                if(!is_null($get_exam_student->exam_marks)){
                      foreach ($get_exam_student->exam_marks->toArray() as $value) 
                      {
                            if(!is_null(SubjectGrading::get_a_level_division_gradings($value['marks_taken']))){
                                $stud_avg_marks[$value['class_id']][SubjectGrading::get_a_level_division_gradings($value['marks_taken'])][] = $value['student_id'];
                            }
                       }
                }
            }
        }
        $data['stud_avg_marks'] = $stud_avg_marks;
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_level', 'advanced_level')->get();
        $data['title'] = 'A level performance report';
        return view('admin.system-reports.division_by_table_reports',$data);
    }

    public function divisionByGraphReports(Request $request)
    {
        if(count($_GET) > 0){
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $get_exam_students = $data['get_exam_students'] = StudentDetail::with(['exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();
            $data['get_exam_class'] = SubjectClass::where('id', $request['class_id'])->first();
            $stud_avg_marks = array();
           
            if(isset($get_exam_students) && $get_exam_students->count() > 0)
            {
                foreach($get_exam_students as $get_exam_student)
                {
                    if(!is_null($get_exam_student->exam_marks)){
                          foreach ($get_exam_student->exam_marks->toArray() as $value) 
                          {
                                if(!is_null(SubjectGrading::get_a_level_division_gradings($value['marks_taken']))){
                                    $stud_avg_marks[SubjectGrading::get_a_level_division_gradings($value['marks_taken'])][] = $value['student_id'];
                                }
                           }
                    }
                }
            }
            $data['stud_avg_marks'] = $stud_avg_marks;
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_level', 'advanced_level')->get();
        $data['title'] = 'A level performance report';
        return view('admin.system-reports.division_by_graph_reports',$data);
    }

    public function divisionByGenderReports(Request $request)
    {
        if(count($_GET) > 0){
            $whr_exam_stud['term'] = $_GET['term_id'];
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $get_exam_students = $data['get_exam_students'] = StudentDetail::with(['exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();
            $stud_avg_marks = array();
           
            if(isset($get_exam_students) && $get_exam_students->count() > 0)
            {
                foreach($get_exam_students as $get_exam_student)
                {
                    if(!is_null($get_exam_student->exam_marks)){
                          foreach ($get_exam_student->exam_marks->toArray() as $value) 
                          {
                                if(!is_null(SubjectGrading::get_a_level_division_gradings($value['marks_taken']))){
                                    if($request->type == 'gender'){
                                        $stud_avg_marks[$get_exam_student->class_id][$get_exam_student->gender][SubjectGrading::get_a_level_division_gradings($value['marks_taken'])][] = $value['student_id'];    
                                    }
                                    elseif($request->type == 'residential'){
                                         $stud_avg_marks[$get_exam_student->class_id][$get_exam_student->residential_status][SubjectGrading::get_a_level_division_gradings($value['marks_taken'])][] = $value['student_id']; 
                                    }
                                }
                           }
                    }
                }
            }
            $data['stud_avg_marks'] = $stud_avg_marks;
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_level', 'advanced_level')->get();
        $data['title'] = 'A level performance report';
        return view('admin.system-reports.division_by_gender_reports',$data);
    }

    public function subjectsByTableReports(Request $request)
    {
        if(count($_GET) > 0){
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $data['get_exam_students'] = StudentDetail::with(['exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();
            $data['get_exam_class'] = SubjectClass::where('id', $request['class_id'])->first();
            $whr_exam_set['class_id'] = $_GET['class_id'];
            $whr_exam_set['mark_set_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';
            $data['get_exam_sets'] = ClassSetMark::with(['exam_set_details'])->where($whr_exam_set)->whereHas('exam_set_details',function($query) use ($data){ $query->where('set_curriculam', $data['get_exam_class']->class_curriculam);$query->where('set_status', 'active');$query->where('is_deleted', 'no'); })->get();
        }
        $data['title'] = 'Subject By Table';
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_level', 'advanced_level')->get();
        $data['get_exam_subjects'] = Subject::where('subject_study_level','advanced_level')->where('subject_staus','active')->get();
        return view('admin.system-reports.subjects_by_table_reports', $data);
    }
    
    public function subjectsByGraphReports(Request $request)
    {
        if(count($_GET) > 0){
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            //$whr_exam_stud['subject_id'] = $request['subject_id'];
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $get_exam_students = StudentDetail::with(['exam_marks.staff_details','class','stream'])
            ->whereHas('exam_marks',function($query)use($request){
                    $query->where('subject_id', $request->subject_id);
            })->where($whr_exam_stud)->get();

            $stud_avg_marks = array();
            $data['get_exam_class'] = SubjectClass::where('id', $request['class_id'])->first();
            $data['get_exam_subject'] = Subject::where('id', $request['subject_id'])->first();

            if(isset($get_exam_students) && $get_exam_students->count() > 0)
            {
                foreach($get_exam_students as $get_exam_student)
                {
                    if(!is_null($get_exam_student->exam_marks)){
                        foreach ($get_exam_student->exam_marks->toArray() as $value) 
                          {
                            if(!is_null(SubjectGrading::getGradings($value['average_total'],$data['get_exam_class']->class_level)))
                            {
                                $stud_avg_marks[SubjectGrading::getGradings($value['average_total'],$data['get_exam_class']->class_level)->grade][] = $value['student_id'];    
                            }
                                
                        }
                    }
                }
            }
            $data['stud_avg_marks'] = $stud_avg_marks;
            //dd($data['stud_avg_marks']);
            $whr_exam_set['class_id'] = $_GET['class_id'];
            $whr_exam_set['mark_set_status'] = 'active';
            $whr_exam_set['is_deleted'] = 'no';
            $data['get_papers'] = SubjectPaper::with(['subject'])->where('paper_staus','active')->get();
            $data['get_exam_gradings'] = SubjectGrading::where('study_level',$data['get_exam_class']->class_level)->where('grade_status','active')->where('is_deleted','no')->orderBy('grade','DESC')->get()->pluck('grade','grade')->toArray();
            //dd($data['get_exam_gradings']);
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_level', 'advanced_level')->get();
        $data['get_exam_subjects'] = Subject::where('subject_study_level','advanced_level')->where('subject_staus','active')->get();
        return view('admin.system-reports.subjects_by_graph_reports',$data);
    }

    public function summarySheetReport(Request $request)
    {
        if(count($_GET) > 0){
            $whr_exam_stud['class_id'] = $_GET['class_id'];
            $whr_exam_stud['student_staus'] = 'active';
            $whr_exam_stud['is_deleted'] = 'no';
            $data['get_exam_students'] = StudentDetail::with(['exam_marks.staff_details','class','stream'])->where($whr_exam_stud)->get();
            $data['get_exam_class'] = SubjectClass::where('id', $request['class_id'])->first();
        }
        $data['class_lists'] = SubjectClass::where('is_deleted', 'no')->where('class_status','active')->where('class_level', 'advanced_level')->get();
        $data['exam_sets'] = ExamSet::where('is_deleted','no')->where('set_curriculam','old_curriculam')->where('set_status','active')->get();
        $data['title'] = 'Class Performance Report';
        return view('admin.system-reports.summary_reports',$data);
    }
    
}
