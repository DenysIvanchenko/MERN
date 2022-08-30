<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'stream_name',
        'stream_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function class_details()
    {
       return $this->belongsTo(SubjectClass::class, 'class_id');
    }
}
