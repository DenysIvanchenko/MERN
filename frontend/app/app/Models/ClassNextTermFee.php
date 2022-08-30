<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassNextTermFee extends Model
{
    use HasFactory;

    protected $table = 'class_next_term_feeses';

    protected $fillable = [
        'class_id',
        'day_fees',
        'boarding_fees',
        'fee_status',
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
