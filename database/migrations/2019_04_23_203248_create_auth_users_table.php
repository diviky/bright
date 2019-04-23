<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable()->index('parent_id');
            $table->string('role', 50)->nullable()->index('role');
            $table->string('name', 50)->nullable();
            $table->string('username', 25)->unique('auth_users_username_unique');
            $table->string('email', 191)->nullable();
            $table->string('password', 191);
            $table->string('mobile', 15)->nullable();
            $table->string('access_token', 100)->nullable()->index('auth_users_token_index');
            $table->string('google2fa_secret', 50)->nullable();
            $table->string('avatar', 191)->nullable();
            $table->text('options', 65535)->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('last_password_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('last_login_at')->nullable();
            $table->string('last_login_ip', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->boolean('status')->default(0)->index('auth_users_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('auth_users');
    }
}
