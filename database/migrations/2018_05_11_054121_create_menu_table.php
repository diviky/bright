<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addon_menu_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable()->index();
            $table->unsignedInteger('type_id')->index();

            $table->string('name', 50)->nullable();
            $table->string('title', 50)->nullable();
            $table->string('url', 255)->nullable();
            $table->text('meta')->nullable();
            $table->boolean('access')->default(1);
            $table->unsignedInteger('ordering')->default(1);
            $table->boolean('status')->default(1)->index();
            $table->timestamps();

            $table->foreign('type_id')
                ->references('id')
                ->on('addon_menu')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addon_menu_items');
    }
}
