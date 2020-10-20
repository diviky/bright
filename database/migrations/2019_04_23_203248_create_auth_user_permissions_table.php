<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('bright.table.user_permissions'), function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->string('model_type', 191);
            $table->foreignId('model_id');
            $table->boolean('is_exclude')->default(0);
            $table->primary(['permission_id', 'model_id', 'model_type']);
            $table->index(['model_type', 'model_id'], 'user_permissions_model_type_model_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('bright.table.user_permissions'));
    }
}
