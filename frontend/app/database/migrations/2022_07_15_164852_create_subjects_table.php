<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_name')->nullable();
            $table->string('subject_code')->nullable();
            $table->enum('subject_core',['yes', 'no'])->nullable();
            $table->enum('subject_curriculam',['new_curriculam', 'old_curriculam'])->nullable();
            $table->enum('subject_study_level',['ordinary_level', 'advanced_level'])->nullable();
            $table->string('subject_compulsory_papers')->nullable();
            $table->enum('subject_staus',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('subjects');
    }
}
