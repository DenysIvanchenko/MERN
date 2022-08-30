<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_details', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('staff_name')->nullable();
            $table->enum('staff_gender', ['male', 'female', 'others'])->nullable();
            $table->string('staff_date_of_birth')->nullable();
            $table->enum('staff_marital_status', ['single', 'married', 'divorced'])->nullable();
            $table->enum('staff_religious_affiliation', ['catholic', 'protestant', 'moslem', 'orthodox', 'others'])->nullable();
            $table->string('staff_next_of_kin')->nullable();
            $table->string('staff_next_of_kin_contact')->nullable();
            $table->string('staff_nationality')->nullable();
            $table->string('staff_home_address')->nullable();
            $table->string('staff_contact_one')->nullable();
            $table->string('staff_contact_two')->nullable();
            $table->string('staff_email')->nullable();
            $table->enum('staff_high_level_education', ['phd','masters_degree','bachelors_degree','diploma','certificate','none','others'])->nullable();
            $table->string('staff_year_of_joining_in_school')->nullable();
            $table->enum('staff_type',['teaching_staff', 'non_teaching_staff'])->nullable();
            $table->integer('staff_category_id')->nullable();
            $table->longText('staff_teaching_subjects')->nullable();
            $table->string('staff_initial')->nullable();
            $table->enum('staff_teaching_experience',['less_than_1_year','1_to_2_years','2_to_5_years','more_than_years'])->nullable();
            $table->enum('staff_position',['dos','head_teacher','directors','fulltime_teacher','parttime_teacher'])->nullable();
            $table->enum('staff_contract_type',['internship','temporary_contract','permanent_contract'])->nullable();
            $table->enum('staff_staus',['active', 'inactive'])->default('active');
            $table->enum('is_deleted',['yes', 'no'])->default('no');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_details');
    }
}
