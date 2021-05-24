<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAuthUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('bright.table.user_permissions'), function (Blueprint $table): void {
            $table->foreign('permission_id', 'user_permissions_permission_id_foreign')->references('id')->on(config('bright.table.permissions'))->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('bright.table.user_permissions'), function (Blueprint $table): void {
            $table->dropForeign('user_permissions_permission_id_foreign');
        });
    }
}
