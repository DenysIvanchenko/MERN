<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToStaffDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staff_details', function (Blueprint $table) {
            $table->string('number_of_children')->nullable();
            $table->longText('profile_picture')->nullable();
            $table->string('district')->nullable();
            $table->string('country')->nullable();
            $table->string('sub_country')->nullable();
            $table->string('parish')->nullable();
            $table->string('village')->nullable();
            $table->longText('classes_you_teach')->nullable();
            $table->string('parent_father_name')->nullable();
            $table->string('parent_father_alive')->nullable();
            $table->string('parent_father_occupation')->nullable();
            $table->string('parent_mother_name')->nullable();
            $table->string('parent_mother_alive')->nullable();
            $table->string('parent_mother_occupation')->nullable();
            $table->string('having_health_problem')->nullable();
            $table->string('health_problem')->nullable();
            $table->longText('staff_documents')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staff_details', function (Blueprint $table) {
            //
        });
    }
}
