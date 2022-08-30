<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectPaper extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'paper_name',
        'paper_code',
        'paper_compulsary',
        'paper_description',
        'paper_staus',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
