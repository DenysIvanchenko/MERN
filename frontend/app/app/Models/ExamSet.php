<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSet extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'set_curriculam',
        'set_name',
        'set_short_name',
        'set_description',
        'set_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];
}
