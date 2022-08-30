<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentIndiciplineCase extends Model
{
    use HasFactory;

    protected $table = 'student_indicipline_cases';

    protected $fillable = [
        'case_date',
        'student_id',
        'indicipline_category',
        'indicipline_rating',
        'handled_by',
        'action_taken',
        'description',
        'case_status',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    
    public function student(){
        return $this->belongsTo(StudentDetail::class, 'student_id');
    }

    public function staff(){
        return $this->belongsTo(StaffDetail::class, 'handled_by');
    }

    public function category(){
        return $this->belongsTo(StudentIndiciplineCategory::class, 'indicipline_category');
    }

    public function rating(){
        return $this->belongsTo(StudentIndiciplineRating::class, 'indicipline_rating');
    }
}
