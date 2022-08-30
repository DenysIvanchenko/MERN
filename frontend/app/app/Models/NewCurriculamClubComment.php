<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewCurriculamClubComment extends Model
{
    use HasFactory;

    //protected $table = 'new_curriculam_games_and_sports_comments';

    protected $fillable = [
        'student_id',
        'class_id',
        'paper_id',
        'term_id',
        'year',
        'comments',
        'exam_report_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];
}
