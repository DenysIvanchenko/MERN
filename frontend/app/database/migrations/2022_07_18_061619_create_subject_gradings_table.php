<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectGradingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subject_gradings', function (Blueprint $table) {
            $table->id();
            $table->enum('study_level',['ordinary_level', 'advanced_level'])->nullable();
            $table->string('marks_from')->nullable();
            $table->string('marks_to')->nullable();
            $table->string('grade')->nullable();
            $table->string('comments')->nullable();
            $table->enum('grade_status',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('subject_gradings');
    }
}
