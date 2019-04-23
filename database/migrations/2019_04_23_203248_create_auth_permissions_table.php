<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 191);
            $table->string('display_name', 50)->nullable();
            $table->string('guard_name', 191);
            $table->timestamps();
            $table->boolean('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('auth_permissions');
    }
}
