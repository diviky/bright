<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAddonMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('addon_menu_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable()->index('addon_menu_items_parent_id_index');
            $table->integer('type_id')->unsigned()->index('addon_menu_items_type_id_index');
            $table->string('name', 50)->nullable();
            $table->string('title', 50)->nullable();
            $table->string('url')->nullable();
            $table->text('meta', 65535)->nullable();
            $table->boolean('access')->default(1);
            $table->integer('ordering')->unsigned()->default(1);
            $table->boolean('status')->default(1)->index('addon_menu_items_status_index');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('addon_menu_items');
    }
}
