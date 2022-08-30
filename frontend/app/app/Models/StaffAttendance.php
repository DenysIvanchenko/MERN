<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    use HasFactory;

     protected $fillable = [
        'staff_id',
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

    public function staff(){
        return $this->belongsTo(StaffDetail::class, 'staff_id');
    }
}
