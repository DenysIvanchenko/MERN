<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExam extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'class_id',
        'subject_id',
        'paper_id',
        'combination_id',
        'stream_id',
        'term_id',
        'year',
        'total_students',
        'total_sets',
        'exam_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function student_exam_marks()
    {
        return $this->hasMany(StudentExamMark::class, 'exam_id');
    }
}
