<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class SubjectGrading extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_level',
        'marks_from',
        'marks_to',
        'grade',
        'grade_code',
        'comments',
        'grade_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public static function getGradings($avg_mark, $study_level)
    {   $avg_mark = round($avg_mark);
        // print_r($avg_mark,$study_level);
        $select_statement = DB::table('subject_gradings')->where('study_level',$study_level)->where('marks_from','<=',$avg_mark)->where('marks_to','>=',$avg_mark)->value('grade_code');
        return $select_statement;
    }
    public static function getGradingComments($avg_mark, $study_level)
    {
       $avg_mark = round($avg_mark);
        $select_statement = DB::table('subject_gradings')->where('study_level',$study_level)->where('marks_from','<=',$avg_mark)->where('marks_to','>=',$avg_mark)->value('comments');
        return $select_statement;
    }
    public static function getGrade($avg_mark, $study_level)
    {
        $avg_mark = round($avg_mark);
        $select_statement = DB::table('subject_gradings')->where('study_level',$study_level)->where('marks_from','<=',$avg_mark)->where('marks_to','>=',$avg_mark)->value('grade');
        return $select_statement;
    }

    public static function get_a_level_gradings($avg_mark){
        $grade_title = null;
        if(in_array($avg_mark, range(0,40))){
            $grade_title = 'F';
        }
        elseif(in_array($avg_mark, range(40,50))){
            $grade_title = 'O';
        }elseif(in_array($avg_mark, range(50,60))){
            $grade_title = 'E';
        }elseif(in_array($avg_mark,range(60,64))){
            $grade_title = 'D';
        }elseif(in_array($avg_mark,range(65,69))){
            $grade_title = 'C';
        }elseif(in_array($avg_mark,range(70,74))){
            $grade_title = 'B';
        }elseif(in_array($avg_mark,range(75,100))){
            $grade_title = 'A';
        }
        return $grade_title;
    }

    public static function get_a_level_division_gradings($avg_mark){
        $grade_title = null;
        if(in_array($avg_mark, range(0,8))){
            $grade_title = 'DIV 1';
        }
        elseif(in_array($avg_mark, range(8,32))){
            $grade_title = 'DIV 1';
        }elseif(in_array($avg_mark, range(33,44))){
            $grade_title = 'DIV 2';
        }elseif(in_array($avg_mark,range(45,51))){
            $grade_title = 'DIV 3';
        }elseif(in_array($avg_mark,range(51,59))){
            $grade_title = 'DIV 4';
        }
        return $grade_title;
    }

    public static function get_class_5_and_6_gradings($marks,$study_level,$not_subsidiary){
        $grades = array();
        foreach($marks as $mark){
            $mark = round($mark);
            $grade = DB::table('subject_gradings')->where('study_level',$study_level)->where('marks_from','<=',$mark)->where('marks_to','>=',$mark)->value('grade');
            array_push($grades,$grade);
        }
        rsort($grades);
        $calc_grade = 0;
        if(count($grades) == 3){
            if($grades[0] < 3){
                $calc_grade = 1;
            }else if($grades[0] <=7){
                if($grades[0] == $grades[1]){
                    $calc_grade = $grades[0]-1;
                }else{
                    $calc_grade = $grades[0]-2;
                }
            }else if($grades[0] == 8){
                if($grades[1] < 7){
                    $calc_grade = 5;
                }else{
                    $calc_grade = 6;
                }
            }
            else{
                if($grades[1] < 7){
                    $calc_grade = 6;
                }else{
                    $calc_grade = 7;
                }
            }
        }else{
            if($grades[0] < 3){
                $calc_grade = 1;
            }else if($grades[0] <= 7){
                $calc_grade = $grades[0]-2;
            }else if($grades[0] == 8){
                if($grades[1] <7){
                    $calc_grade = 6;
                }else{
                    $calc_grade = 6;
                }
            }else{
                $calc_grade = 7;
            }
        }

        $comments = array('A','B','C','D','E','O','F');

        if($not_subsidiary == 'no'){
            if($calc_grade < 7){
                $calc_grade = 6;
            }
        }

        $result['grade'] = $comments[$calc_grade-1];
        $result['points'] = (7-$calc_grade)/count($grades);
        return $result;
    }
}
