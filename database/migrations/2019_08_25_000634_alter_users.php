<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Add2faToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auth_users', function (Blueprint $table) {
            $table->integer('parent_id')->unsigned()->nullable()->index('parent_id')->after('id');
            $table->string('role', 50)->nullable()->index('role')->after('role');
            $table->string('mobile', 15)->nullable()->after('password');
            $table->string('avatar', 191)->nullable()->after('mobile');
            $table->string('google2fa_secret', 50)->nullable()->after('remember_token');
            $table->json('options')->nullable();
            $table->string('access_token', 100)->nullable()->index('auth_users_token_index');
            $table->timestamp('last_password_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('last_login_at')->nullable();
            $table->string('last_login_ip', 20)->nullable();
            $table->softDeletes();
            $table->boolean('status')->default(0)->index('auth_users_status_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auth_users', function (Blueprint $table) {
            $table->dropColumn('google2fa_secret');
            $table->dropColumn('parent_id');
            $table->dropColumn('role');
            $table->dropColumn('mobile');
            $table->dropColumn('avatar');
            $table->dropColumn('options');
            $table->dropColumn('access_token');
            $table->dropColumn('last_password_at');
            $table->dropColumn('last_login_at');
            $table->dropColumn('last_login_ip');
            $table->dropSoftDeletes();
            $table->dropColumn('status');
        });
    }
}
