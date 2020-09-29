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
        Schema::table(config('karla.table.password_history'), function (Blueprint $table) {
            $table->foreign('user_id', 'password_history')->references('id')->on(config('karla.table.users'))->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(config('karla.table.password_history'), function (Blueprint $table) {
            $table->dropForeign('password_history');
        });
    }
}
