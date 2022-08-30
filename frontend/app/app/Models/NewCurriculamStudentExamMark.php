<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewCurriculamStudentExamMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'staff_id',
        'exam_id',
        'class_id',
        'subject_id',
        'paper_id',        
        'exam_set_id',
        'term_id',
        'year',
        'general_skills',
        'general_remarks',
        'exam_score',
        'exam_marks_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function exam_set_details()
    {
       return $this->belongsTo(NewCurriculamCompetency::class, 'exam_set_id');
    }

    public function mark_details()
    {
        return $this->belongsTo(ClassSetMark::class, 'set_id');
    }

    public function student_details()
    {
        return $this->belongsTo(StudentDetail::class, 'student_id');
    }

    public function staff_details()
    {
        return $this->belongsTo(StaffDetail::class, 'staff_id');
    }
}
