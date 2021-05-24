<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAuthUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('bright.table.user_roles'), function (Blueprint $table): void {
            $table->foreign('role_id', 'user_roles_role_id_foreign')->references('id')->on(config('bright.table.roles'))->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('model_id', 'user_roles_ibfk_1')->references('id')->on(config('bright.table.users'))->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('bright.table.user_roles'), function (Blueprint $table): void {
            $table->dropForeign('user_roles_role_id_foreign');
            $table->dropForeign('user_roles_ibfk_1');
        });
    }
}
