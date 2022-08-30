<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentIndiciplineCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_title',
        'category_punishment',
        'indicipline_category_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];
}
