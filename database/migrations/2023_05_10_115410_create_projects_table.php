<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->date('due_date');
            $table->text('summary');
            $table->string('status', 255);
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('reminder_id');
            $table->unsignedBigInteger('team_id');
            $table->timestamps();

            $table->foreign('document_id')->references('id')->on('documents');
            $table->foreign('reminder_id')->references('id')->on('reminds');
            $table->foreign('team_id')->references('id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
