<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->boolean('assignee_changes')->default(true);
            $table->boolean('status_cahnges')->default(true);
            $table->boolean('tasks_assigned_to_me')->default(false);
            $table->boolean('document_edited')->default(false);
            $table->boolean('new_version_published')->default(false);
            $table->boolean('due_date_changes')->default(true);
            $table->boolean('due_date_overdue')->default(true);
            $table->boolean('before_due_date_reminder')->default(false);

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
