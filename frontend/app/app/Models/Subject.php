<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_name',
        'subject_code',
        'subject_core',
        'subject_curriculam',
        'subject_study_level',
        'not_subsidiary',
        'subject_compulsory_papers',
        'subject_staus',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function subject_compentency(){
        return $this->hasMany(NewCurriculamCompetency::class, 'subject_id');
    }

    public function subject_papers(){
        return $this->hasMany(SubjectPaper::class, 'subject_id');
    }
}
