<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewCurriculamCompetency extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'subject_id',
        'term_id',
        'unit_set_id',
        'competency_description',
        'competency_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

     public function class(){
        return $this->belongsTo(SubjectClass::class, 'class_id');
    }

     public function subject(){
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function exam_set(){
        return $this->belongsTo(ExamSet::class, 'unit_set_id');
    }
}
