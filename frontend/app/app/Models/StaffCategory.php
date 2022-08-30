<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'category_description',
        'staff_type',
        'staff_section',
        'role_id',
        'category_staus',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];
}
