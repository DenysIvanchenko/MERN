<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SujectOptional extends Model
{
    use HasFactory;

    protected $table = 'subject_optionals';

     protected $fillable = [
        'class_id',
        'term_id',
        'year',
        'student_id',
        'subject_one',
        'subject_two',
        'subject_three',
        'optional_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];
}
