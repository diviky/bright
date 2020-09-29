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
        Schema::table(config('karla.table.tokens'), function (Blueprint $table) {
            $table->foreign('user_id', 'tokens_user_id_foreign')->references('id')->on(config('karla.table.users'))->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(config('karla.table.tokens'), function (Blueprint $table) {
            $table->dropForeign('tokens_user_id_foreign');
        });
    }
}
