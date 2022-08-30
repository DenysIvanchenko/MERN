<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentIndiciplineTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_indicipline_ratings', function (Blueprint $table) {
            $table->id();
            $table->string('rating_category')->nullable();
            $table->enum('rating_category_status',['active', 'inactive'])->default('active');
            $table->enum('is_deleted',['yes', 'no'])->default('no');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('student_indicipline_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_title')->nullable();
            $table->longText('category_punishment')->nullable();
            $table->enum('indicipline_category_status',['active', 'inactive'])->default('active');
            $table->enum('is_deleted',['yes', 'no'])->default('no');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('student_indicipline_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_date')->nullable();
            $table->integer('student_id')->nullable();
            $table->integer('indicipline_category')->nullable();
            $table->integer('indicipline_rating')->nullable();
            $table->integer('handled_by')->nullable();
            $table->longText('action_taken')->nullable();
            $table->longText('description')->nullable();
            $table->enum('case_status',['active', 'inactive'])->default('active');
            $table->enum('is_deleted',['yes', 'no'])->default('no');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('generalsettings', function (Blueprint $table) {
            $table->id();
            $table->string('current_study_year')->nullable();
            $table->string('current_study_term')->nullable();
            $table->string('contact_one')->nullable();
            $table->string('contact_two')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_address')->nullable();
            $table->string('term_ends_on')->nullable();
            $table->string('next_term_begins_on')->nullable();
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
        Schema::dropIfExists('student_indicipline_ratings');
        Schema::dropIfExists('student_indicipline_categories');
        Schema::dropIfExists('student_indicipline_cases');
        Schema::dropIfExists('generalsettings');
    }
}
