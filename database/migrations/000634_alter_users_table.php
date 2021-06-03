<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('bright.table.users'), function (Blueprint $table): void {
            $table->bigInteger('parent_id')->unsigned()->nullable()->index('parent_id')->after('id');
            $table->string('role', 50)->nullable()->index('role')->after('parent_id');
            $table->string('mobile', 15)->nullable()->after('password');
            $table->string('avatar', 191)->nullable()->after('mobile');
            $table->string('access_token', 100)->nullable()->unique()->index('users_access_token')->after('password');
            $table->string('api_token', 80)->nullable()->unique()->index('users_api_token')->after('password');
            $table->text('options')->nullable()->after('access_token');
            $table->timestamp('last_password_at')->nullable()->after('remember_token');
            $table->timestamp('last_login_at')->nullable()->after('last_password_at');
            $table->string('last_login_ip', 20)->nullable()->after('last_login_at');
            $table->softDeletes()->after('updated_at');
            $table->boolean('status')->default(0)->index('users_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('bright.table.users'), function (Blueprint $table): void {
            $table->dropColumn('parent_id');
            $table->dropColumn('role');
            $table->dropColumn('mobile');
            $table->dropColumn('avatar');
            $table->dropColumn('options');
            $table->dropColumn('access_token');
            $table->dropColumn('api_token');
            $table->dropColumn('last_password_at');
            $table->dropColumn('last_login_at');
            $table->dropColumn('last_login_ip');
            $table->dropSoftDeletes();
            $table->dropColumn('status');
        });
    }
}
