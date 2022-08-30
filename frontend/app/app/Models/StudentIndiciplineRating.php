<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentIndiciplineRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'rating_category',
        'rating_category_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];
}
