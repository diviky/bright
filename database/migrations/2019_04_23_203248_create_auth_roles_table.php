<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthRolesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191)->index('name');
            $table->string('guard_name', 191);
            $table->string('display_name', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('auth_roles');
    }
}
