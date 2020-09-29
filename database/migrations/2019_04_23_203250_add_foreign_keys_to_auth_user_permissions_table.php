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
        Schema::table(config('karla.table.user_permissions'), function (Blueprint $table) {
            $table->foreign('permission_id', 'user_permissions_permission_id_foreign')->references('id')->on(config('karla.table.permissions'))->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(config('karla.table.user_permissions'), function (Blueprint $table) {
            $table->dropForeign('user_permissions_permission_id_foreign');
        });
    }
}
