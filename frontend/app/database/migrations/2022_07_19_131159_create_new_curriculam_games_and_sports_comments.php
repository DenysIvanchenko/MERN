<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewCurriculamGamesAndSportsComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_curriculam_games_and_sports_comments', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->nullable();
            $table->string('class_id')->nullable();
            $table->string('paper_id')->nullable();
            $table->string('term_id')->nullable();
            $table->string('year')->nullable();
            $table->longText('comments')->nullable();
            $table->enum('exam_report_status',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('new_curriculam_games_and_sports_comments');
    }
}
