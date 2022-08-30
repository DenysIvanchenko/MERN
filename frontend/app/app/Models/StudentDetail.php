<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_name',
        'registration_no',
        'date_of_birth',
        'gender',
        'nationality',
        'district',
        'address',
        'entry_date',
        'profile_picture',
        'parent_id',
        'class_id',
        'stram_id',
        'term',
        'year_of_study',
        'entry_status',
        'residential_status',
        'student_staus',
        'is_deleted',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
        'admission_number',
        'identification_number',
        'house_details',
        'former_school',
        'aggregate_obtained_in',
        'division',
        'religion',
        'aggregate',
        'former_school_responsibility',
        'having_health_problem',
        'health_problem',
        'is_baptised_catholic',
        'is_first_holy_communion',
        'received_confirmation',
        'parish_come_from',
        'discese_parish',
        'parish_priest_name',
        'parish_priest_contact',
        'a_level_subjects',
        'languages_known',
        'hobbies',
        'parent_father_name',
        'parent_father_alive',
        'parent_father_occupation',
        'parent_father_residence',
        'parent_father_email',
        'parent_father_telephone',
        'parent_mother_name',
        'parent_mother_alive',
        'parent_mother_occupation',
        'parent_mother_residence',
        'parent_telephone',
        'parent_email',
        'guardian_name',
        'guardian_telephone',
        'who_will_pay_your_fee',
        'parent_documents',
    ];

    public function class(){
        return $this->belongsTo(SubjectClass::class, 'class_id');
    }

    public function stream(){
        return $this->belongsTo(Stream::class, 'stram_id');
    }

    public function parents(){
        return $this->belongsTo(StudentParent::class, 'parent_id');
    }

    public function staff_attendance()
    {
        return $this->hasMany(StudentDetail::class, 'student_id');
    }

    public function student_attendance()
    {
        return $this->hasMany(StudentDetail::class, 'student_id');
    }

    public function exam_marks(){
        return $this->hasMany(StudentExamMark::class, 'student_id');
    }

    public function new_curriculam_exam_marks(){
        return $this->hasMany(NewCurriculamStudentExamMark::class, 'student_id');
    }
}


