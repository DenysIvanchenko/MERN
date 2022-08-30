<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCombinationsAndSubsidiariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subject_combinations', function (Blueprint $table) {
            $table->id();
            $table->string('combination_name')->nullable();
            $table->integer('subject_one')->nullable();
            $table->integer('subject_two')->nullable();
            $table->integer('subject_three')->nullable();
            $table->enum('type',['arts','science'])->nullable();
            $table->integer('subsidiary_one')->nullable();
            $table->integer('subsidiary_two')->nullable();
            $table->enum('combination_status',['active', 'inactive'])->default('active');
            $table->enum('is_deleted',['yes', 'no'])->default('no');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('subject_subsidiaries', function (Blueprint $table) {
            $table->id();
            $table->string('subject_name')->nullable();
            $table->string('short_code')->nullable();
            $table->string('number_of_papers')->nullable();
            $table->enum('subsidiary_status',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('subject_combinations');
        Schema::dropIfExists('subject_subsidiaries');
    }
}
