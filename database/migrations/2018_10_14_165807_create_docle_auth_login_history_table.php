<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocleAuthLoginHistoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_login_history', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->integer('user_id')->unsigned()->index('user_id');
            $table->string('ip', 20)->nullable();
            $table->text('ips')->nullable();
            $table->string('host', 128)->nullable();
            $table->string('user_agent')->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();
            $table->boolean('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('auth_login_history');
    }
}
