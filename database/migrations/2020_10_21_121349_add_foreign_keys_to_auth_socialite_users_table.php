<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAuthSocialiteUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table(config('bright.table.socialite_users'), function (Blueprint $table) {
            $table->foreign('user_id', 'socialite_users_ibfk_1')->references('id')->on(config('bright.table.users'))->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(config('bright.table.socialite_users'), function (Blueprint $table) {
            $table->dropForeign('socialite_users_ibfk_1');
        });
    }
}
