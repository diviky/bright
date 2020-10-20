<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('bright.table.user_roles'), function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->string('model_type', 191);
            $table->foreignId('model_id')->index('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
            $table->index(['model_type', 'model_id'], 'user_roles_model_type_model_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('bright.table.user_roles'));
    }
}
