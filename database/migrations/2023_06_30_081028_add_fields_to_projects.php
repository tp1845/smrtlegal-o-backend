<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('members')->after('team_id')->nullable();
            $table->json('signatory')->after('team_id')->nullable();
            $table->json('external_collaborators')->after('team_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('members');
            $table->dropColumn('signatory');
            $table->dropColumn('external_collaborators');
        });
    }
}
