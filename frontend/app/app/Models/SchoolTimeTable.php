<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolTimeTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'day',
        'staff_id',
        'class_id',
        'stream_id',
        'subject_id',
        'start_time',
        'end_time',
        'timetable_status',
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

    public function class(){
        return $this->belongsTo(SubjectClass::class, 'class_id');
    }

    public function subject(){
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function stream(){
        return $this->belongsTo(Subject::class, 'stream_id');
    }
}
