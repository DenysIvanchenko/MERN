<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToStudentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_details', function (Blueprint $table) {
            $table->string('admission_number')->nullable();
            $table->string('identification_number')->nullable();
            $table->string('house_details')->nullable();
            $table->string('former_school')->nullable();
            $table->string('aggregate_obtained_in')->nullable();
            $table->string('division')->nullable();
            $table->string('religion')->nullable();
            $table->string('aggregate')->nullable();
            $table->string('former_school_responsibility')->nullable();
            $table->string('having_health_problem')->nullable();
            $table->string('health_problem')->nullable();
            $table->string('is_baptised_catholic')->nullable();
            $table->string('is_first_holy_communion')->nullable();
            $table->string('received_confirmation')->nullable();
            $table->string('parish_come_from')->nullable();
            $table->string('discese_parish')->nullable();
            $table->string('parish_priest_name')->nullable();
            $table->string('parish_priest_contact')->nullable();
            $table->longText('a_level_subjects')->nullable();
            $table->longText('languages_known')->nullable();
            $table->longText('hobbies')->nullable();
            $table->string('parent_father_name')->nullable();
            $table->string('parent_father_alive')->nullable();
            $table->string('parent_father_occupation')->nullable();
            $table->string('parent_father_residence')->nullable();
            $table->string('parent_father_email')->nullable();
            $table->string('parent_father_telephone')->nullable();
            $table->string('parent_mother_name')->nullable();
            $table->string('parent_mother_alive')->nullable();
            $table->string('parent_mother_occupation')->nullable();
            $table->string('parent_mother_residence')->nullable();
            $table->string('parent_telephone')->nullable();
            $table->string('parent_email')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_telephone')->nullable();
            $table->string('who_will_pay_your_fee')->nullable();
            $table->longText('parent_documents')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_details', function (Blueprint $table) {
            //
        });
    }
}
