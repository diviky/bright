<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthPasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_password_resets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 191)->index('auth_password_resets_email_index');
            $table->string('token', 191);
            $table->dateTime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('auth_password_resets');
    }
}
