<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRollCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'roll_call_name',
        'roll_call_description',
        'roll_call_status',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
        'deleted_at',
    ];
}
