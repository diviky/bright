<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAuthTokensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->foreign('user_id', 'auth_tokens_user_id_foreign')->references('id')->on('auth_users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('auth_tokens', function (Blueprint $table) {
            $table->dropForeign('auth_tokens_user_id_foreign');
        });
    }
}
