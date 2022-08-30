<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffLoad extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'class_id',
        'subject_id',
        'paper_id',
        'all_papers',
        'stream_id',
        'all_streams',
        'load_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
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

    public function paper(){
        return $this->belongsTo(SubjectPaper::class, 'paper_id');
    }

    public function stream(){
        return $this->belongsTo(Stream::class, 'stream_id');
    }
}
