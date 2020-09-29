<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAuthActivationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table(config('karla.table.activations'), function (Blueprint $table) {
            $table->foreign('user_id', 'activations_user_id_foreign')->references('id')->on(config('karla.table.users'))->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table(config('karla.table.activations'), function (Blueprint $table) {
            $table->dropForeign('activations_user_id_foreign');
        });
    }
}
