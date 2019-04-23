<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthSocialitesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_socialites', function (Blueprint $table) {
            $table->increments('id');
            $table->string('provider', 20)->unique('provider');
            $table->string('title', 100);
            $table->text('meta')->nullable();
            $table->text('options')->nullable();
            $table->boolean('is_default')->default(0);
            $table->integer('ordering')->default(1);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('auth_socialites');
    }
}
