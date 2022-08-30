<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectCombination extends Model
{
    use HasFactory;

    protected $fillable = [
        'combination_name',
        'subject_one',
        'subject_two',
        'subject_three',
        'type',
        'subsidiary_one',
        'subsidiary_two',
        'combination_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];
    
    public function subject_one(){
        return $this->belongsTo(Subject::class, 'subject_one');
    }
    public function subject_two(){
        return $this->belongsTo(Subject::class, 'subject_two');
    }
    public function subject_three(){
        return $this->belongsTo(Subject::class, 'subject_three');
    }
    public function subject_four(){
        return $this->belongsTo(Subject::class, 'subsidiary_one');
    }
    public function subject_five(){
        return $this->belongsTo(Subject::class, 'subsidiary_two');
    }
}
