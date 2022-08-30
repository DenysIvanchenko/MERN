<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'staff_name',
        'staff_gender',
        'staff_date_of_birth',
        'staff_marital_status',
        'staff_religious_affiliation',
        'staff_next_of_kin',
        'staff_next_of_kin_contact',
        'staff_nationality',
        'staff_home_address',
        'staff_contact_one',
        'staff_contact_two',
        'staff_email',
        'staff_high_level_education',
        'staff_year_of_joining_in_school',
        'staff_type',
        'staff_category_id',
        'staff_teaching_subjects',
        'staff_initial',
        'staff_teaching_experience',
        'staff_position',
        'staff_contract_type',
        'staff_staus',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'number_of_children',
        'profile_picture',
        'district',
        'country',
        'sub_country',
        'parish',
        'village',
        'classes_you_teach',
        'parent_father_name',
        'parent_father_alive',
        'parent_father_occupation',
        'parent_mother_name',
        'parent_mother_alive',
        'parent_mother_occupation',
        'having_health_problem',
        'health_problem',
        'staff_documents',
    ];

    public function category()
    {
        return $this->belongsTo(StaffCategory::class, 'staff_category_id');
    }

    public function staff_attendance()
    {
        return $this->hasMany(StaffAttendance::class, 'staff_id');
    }
}
