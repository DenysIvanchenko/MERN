<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_details', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('student_name')->nullable();
            $table->string('registration_no')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'others'])->nullable();
            $table->string('nationality')->nullable();
            $table->string('district')->nullable();
            $table->string('address')->nullable();
            $table->string('entry_date')->nullable();
            $table->string('profile_picture')->nullable();
            $table->integer('class_id')->nullable();
            $table->integer('stram_id')->nullable();
            $table->enum('term',['term_1','term_2','term_3'])->nullable();
            $table->string('year_of_study')->nullable();
            $table->enum('entry_status',['new', 'continuing'])->nullable();
            $table->enum('residential_status',['day', 'boarding'])->nullable();
            $table->enum('student_staus',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('student_details');
    }
}
