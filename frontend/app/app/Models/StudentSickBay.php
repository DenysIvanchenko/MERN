<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSickBay extends Model
{
    use HasFactory;

    protected $table = 'student_sick_baies';

    protected $fillable = [
        'visit_date',
        'student_id',
        'start_date',
        'complain_description',
        'action_taken',
        'student_sick_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    
    public function student(){
        return $this->belongsTo(StudentDetail::class, 'student_id');
    }
}
