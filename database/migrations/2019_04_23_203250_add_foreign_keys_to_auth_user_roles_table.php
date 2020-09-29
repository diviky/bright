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
        Schema::table(config('karla.table.user_roles'), function (Blueprint $table) {
            $table->foreign('role_id', 'user_roles_role_id_foreign')->references('id')->on(config('karla.table.roles'))->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('model_id', 'user_roles_ibfk_1')->references('id')->on(config('karla.table.users'))->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(config('karla.table.user_roles'), function (Blueprint $table) {
            $table->dropForeign('user_roles_role_id_foreign');
            $table->dropForeign('user_roles_ibfk_1');
        });
    }
}
