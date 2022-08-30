<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentParent extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'student_id',
        'parent_name',
        'parent_phone_one',
        'parent_phone_two',
        'parent_email',
        'parent_gender',
        'parent_address',
        'parent_staus',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function student_details()
    {
        return $this->hasMany(StudentDetail::class, 'parent_id');
    }
}
