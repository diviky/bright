<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAddonMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('addon_menu_items', function (Blueprint $table) {
            $table->foreign('type_id', 'addon_menu_items_type_id_foreign')->references('id')->on('addon_menu')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('addon_menu_items', function (Blueprint $table) {
            $table->dropForeign('addon_menu_items_type_id_foreign');
        });
    }
}
