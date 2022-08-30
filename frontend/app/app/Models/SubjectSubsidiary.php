<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectSubsidiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_name',
        'short_code',
        'paper_code',
        'number_of_papers',
        'subsidiary_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

}
