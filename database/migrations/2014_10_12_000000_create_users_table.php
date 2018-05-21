<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->nullable();
            $table->string('username', 50)->unique();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('mobile', 12)->nullable();
            $table->string('api_token', 60)->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->text('options')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->boolean('status')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_users');
    }
}
