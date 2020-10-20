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
        Schema::create(config('bright.table.user_users'), function (Blueprint $table) {
            $table->foreignId('parent_id');
            $table->foreignId('user_id');
            $table->unique(['parent_id', 'user_id'], 'parent_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('bright.table.user_users'));
    }
}
