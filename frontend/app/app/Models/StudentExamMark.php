<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExamMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'exam_id',
        'class_id',
        'subject_id',
        'paper_id', 
        'combination_id', 
        'term',       
        'stream_id',       
        'staff_id',       
        'year',       
        'exam_set_id',
        'marks_taken',
        'average_total',
        'total_marks',
        'exam_marks_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function exam_set_details()
    {
       return $this->belongsTo(ExamSet::class, 'exam_set_id');
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
    public function combination(){
        return $this->belongsTo(SubjectCombination::class, 'combination_id');
    }
}
