<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_exams', function (Blueprint $table) {
            $table->id();
            $table->integer('class_id')->nullable();
            $table->integer('staff_id')->nullable();
            $table->string('stream_id')->nullable();
            $table->integer('subject_id')->nullable();
            $table->string('paper_id')->nullable();
            $table->string('term_id')->nullable();
            $table->string('year')->nullable();
            $table->string('total_students')->nullable();
            $table->string('total_sets')->nullable();
            $table->enum('exam_status',['active', 'inactive'])->default('active');
            $table->enum('is_deleted',['yes', 'no'])->default('no');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('student_exam_marks', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->nullable();
            $table->integer('exam_id')->nullable();
            $table->integer('subject_id')->nullable();
            $table->integer('class_id')->nullable();
            $table->integer('paper_id')->nullable();
            $table->integer('exam_set_id')->nullable();
            $table->string('marks_taken')->nullable();
            $table->enum('exam_marks_status',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('student_exams');
        Schema::dropIfExists('student_exam_marks');
    }
}
