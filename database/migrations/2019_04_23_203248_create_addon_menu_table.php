<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAddonMenuTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('addon_menu', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('title', 50);
            $table->timestamps();
            $table->boolean('status')->default(1)->index('addon_menu_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('addon_menu');
    }
}
