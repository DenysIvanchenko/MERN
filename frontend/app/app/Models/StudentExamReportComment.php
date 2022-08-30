<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExamReportComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'paper_id',
        'term_id',
        'year',
        'class_teacher_comments',
        'conduct_comments',        
        'hm_comments',
        'exam_report_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];
}
