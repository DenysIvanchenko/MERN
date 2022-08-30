<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'roll_call_id',
        'date',
        'comments',
        'attendance_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
        'deleted_at',
    ];

    public function student(){
        return $this->belongsTo(StudentDetail::class, 'student_id');
    }

    public function roll_call(){
        return $this->belongsTo(StudentRollCall::class, 'roll_call_id');
    }
}
