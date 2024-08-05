<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_user_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id')->index('erp_auth_user_permissions_permission_id_foreign');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->boolean('is_exclude')->default(false);
            $table->unsignedBigInteger('team_id')->default(1)->index('model_has_permissions_team_foreign_key_index');

            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->primary(['team_id', 'permission_id', 'model_id', 'model_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_user_permissions');
    }
};
