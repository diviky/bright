<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthTokensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('karla.table.tokens'), function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('user_id')->index('tokens_user_id_foreign');
            $table->string('access_token', 100)->unique('access_token');
            $table->string('refresh_token', 100)->nullable();
            $table->text('allowed_ip', 65535)->nullable();
            $table->timestamp('expires_in')->nullable();
            $table->timestamps();
            $table->boolean('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('karla.table.tokens'));
    }
}
