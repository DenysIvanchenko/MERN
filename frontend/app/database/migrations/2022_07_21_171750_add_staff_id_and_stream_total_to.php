<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStaffIdAndStreamTotalTo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_exam_marks', function (Blueprint $table) {
            $table->string('term')->after('paper_id')->nullable();
            $table->integer('stream_id')->after('term')->nullable();
            $table->integer('staff_id')->after('stream_id')->nullable();
            $table->string('year')->after('staff_id')->nullable();
            $table->string('average_total')->after('marks_taken')->nullable();
            $table->string('total_marks')->after('average_total')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
