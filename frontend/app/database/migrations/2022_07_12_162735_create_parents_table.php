<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_phone_one')->nullable();
            $table->string('parent_phone_two')->nullable();
            $table->string('parent_email')->nullable();
            $table->enum('parent_gender', ['male', 'female', 'others'])->nullable();
            $table->string('parent_address')->nullable();
            $table->enum('parent_staus',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('parents');
    }
}
