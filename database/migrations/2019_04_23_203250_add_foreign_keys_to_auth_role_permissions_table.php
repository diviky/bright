<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAuthRolePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table(config('karla.table.role_permissions'), function (Blueprint $table) {
            $table->foreign('permission_id', 'role_permissions_permission_id_foreign')->references('id')->on(config('karla.table.permissions'))->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('role_id', 'role_permissions_role_id_foreign')->references('id')->on(config('karla.table.roles'))->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(config('karla.table.role_permissions'), function (Blueprint $table) {
            $table->dropForeign('role_permissions_permission_id_foreign');
            $table->dropForeign('role_permissions_role_id_foreign');
        });
    }
}
