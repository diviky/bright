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
        Schema::table('auth_role_permissions', function (Blueprint $table) {
            $table->foreign('permission_id', 'auth_role_permissions_permission_id_foreign')->references('id')->on('auth_permissions')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('role_id', 'auth_role_permissions_role_id_foreign')->references('id')->on('auth_roles')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('auth_role_permissions', function (Blueprint $table) {
            $table->dropForeign('auth_role_permissions_permission_id_foreign');
            $table->dropForeign('auth_role_permissions_role_id_foreign');
        });
    }
}
