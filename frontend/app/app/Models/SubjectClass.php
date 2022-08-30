<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'class_name',
        'class_prefix',
        'class_level',
        'class_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
        'class_curriculam',
    ];

    public function exam_sets()
    {
       return $this->hasMany(ClassSetMark::class, 'class_id');
    }

    public function next_term_fee()
    {
        return $this->hasOne(ClassNextTermFee::class, 'class_id');
    }

    public function students()
    {
        return $this->hasMany(StudentDetail::class, 'class_id');
    }
}
