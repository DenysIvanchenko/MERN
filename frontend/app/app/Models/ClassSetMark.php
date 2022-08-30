<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSetMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'set_id',
        'mark',
        'mark_set_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function exam_set_details()
    {
       return $this->belongsTo(ExamSet::class, 'set_id');
    }

    public function class_details()
    {
        return $this->belongsTo(SubjectClass::class, 'class_id');
    }
}
