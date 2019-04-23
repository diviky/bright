<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAuthPasswordHistoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('auth_password_history', function (Blueprint $table) {
            $table->foreign('user_id', 'auth_password_history_ibfk_1')->references('id')->on('auth_users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('auth_password_history', function (Blueprint $table) {
            $table->dropForeign('auth_password_history_ibfk_1');
        });
    }
}
