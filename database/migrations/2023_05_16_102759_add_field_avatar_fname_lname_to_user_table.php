<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldAvatarFnameLnameToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->after('name', function (Blueprint $table) {
                $table->string('lname')->nullable();
                $table->string('fname')->nullable();
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['lname', 'fname', 'phone', 'avatar']);
        });
    }
}
