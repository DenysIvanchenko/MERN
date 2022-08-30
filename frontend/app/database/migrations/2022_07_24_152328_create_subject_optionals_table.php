<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectOptionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subject_optionals', function (Blueprint $table) {
            $table->id();
            $table->integer('class_id')->nullable();
            $table->string('term_id')->nullable();
            $table->string('year')->nullable();
            $table->integer('student_id')->nullable();
            $table->integer('subject_one')->nullable();
            $table->integer('subject_two')->nullable();
            $table->integer('subject_three')->nullable();
            $table->enum('optional_status',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('subject_optionals');
    }
}
