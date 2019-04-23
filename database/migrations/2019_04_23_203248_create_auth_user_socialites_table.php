<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthUserSocialitesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_user_socialites', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->integer('user_id')->unsigned()->index('user_id');
            $table->string('provider', 20)->index('provider');
            $table->string('socialite_id', 200)->index('socialite_id');
            $table->string('secret', 50);
            $table->string('nickname', 100)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('email', 200)->nullable();
            $table->text('session_info')->nullable();
            $table->text('profile')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('auth_user_socialites');
    }
}
