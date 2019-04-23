<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAuthUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('auth_user_permissions', function (Blueprint $table) {
            $table->foreign('permission_id', 'auth_user_permissions_permission_id_foreign')->references('id')->on('auth_permissions')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('auth_user_permissions', function (Blueprint $table) {
            $table->dropForeign('auth_user_permissions_permission_id_foreign');
        });
    }
}
