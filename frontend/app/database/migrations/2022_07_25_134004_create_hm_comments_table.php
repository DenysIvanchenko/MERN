<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHmCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hm_comments', function (Blueprint $table) {
            $table->id();
            $table->enum('study_level',['ordinary_level','advanced_level'])->nullable();
            $table->enum('curriculam',['new_curriculam','old_curriculam'])->nullable();
            $table->string('average_marks_from')->nullable();
            $table->string('average_marks_to')->nullable();
            $table->longText('comment')->nullable();
            $table->enum('comments_status',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('hm_comments');
    }
}
