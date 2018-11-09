<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('role', 50)->nullable()->index();
            $table->string('name', 100)->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('mobile', 15)->nullable();
            $table->string('access_token', 60)->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->text('options')->nullable();
            $table->timestamp('last_password_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 100)->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->boolean('status')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('auth_users');
    }
}
