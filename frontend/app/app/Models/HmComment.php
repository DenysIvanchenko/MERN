<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HmComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_level',
        'curriculam',
        'average_marks_from',
        'average_marks_to',
        'comment',
        'comments_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];
}
