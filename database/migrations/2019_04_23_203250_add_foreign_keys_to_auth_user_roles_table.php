<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAuthUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('auth_user_roles', function (Blueprint $table) {
            $table->foreign('role_id', 'auth_user_roles_role_id_foreign')->references('id')->on('auth_roles')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('model_id', 'auth_user_roles_ibfk_1')->references('id')->on('auth_users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('auth_user_roles', function (Blueprint $table) {
            $table->dropForeign('auth_user_roles_role_id_foreign');
            $table->dropForeign('auth_user_roles_ibfk_1');
        });
    }
}
