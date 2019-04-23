<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthActivationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_activations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index('auth_activations_user_id_foreign');
            $table->string('token', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('auth_activations');
    }
}
