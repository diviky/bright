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
        Schema::table('auth_activations', function (Blueprint $table) {
            $table->foreign('user_id', 'auth_activations_user_id_foreign')->references('id')->on('auth_users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('auth_activations', function (Blueprint $table) {
            $table->dropForeign('auth_activations_user_id_foreign');
        });
    }
}
