<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->string('access_token', 100)->nullable();
            $table->string('refresh_token', 100)->nullable();
            $table->text('whitelist_ips')->nullable();
            $table->text('blocklist_ips')->nullable();
            $table->dateTime('expires_in');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('auth_users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('auth_tokens');
    }
}
