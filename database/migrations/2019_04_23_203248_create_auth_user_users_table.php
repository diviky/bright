<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthUserUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_user_users', function (Blueprint $table) {
            $table->integer('parent_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->unique(['parent_id', 'user_id'], 'parent_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('auth_user_users');
    }
}
