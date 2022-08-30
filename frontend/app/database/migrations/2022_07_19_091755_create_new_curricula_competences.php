<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewCurriculaCompetences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_curriculam_competencies', function (Blueprint $table) {
            $table->id();
            $table->integer('class_id')->nullable();
            $table->integer('subject_id')->nullable();
            $table->string('term_id')->nullable();
            $table->integer('unit_set_id')->nullable();
            $table->longText('competency_description')->nullable();
            $table->enum('competency_status',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('new_curriculam_competencies');
    }
}
