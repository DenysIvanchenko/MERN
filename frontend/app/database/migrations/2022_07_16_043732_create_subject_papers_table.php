<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectPapersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subject_papers', function (Blueprint $table) {
            $table->id();
            $table->integer('subject_id')->nullable();
            $table->string('paper_name')->nullable();
            $table->string('paper_code')->nullable();
            $table->enum('paper_compulsary',['yes','no'])->nullable();
            $table->longText('paper_description')->nullable();
            $table->enum('paper_staus',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('subject_papers');
    }
}
