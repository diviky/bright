<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthLoginHistoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('auth_login_history', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->bigInteger('user_id')->unsigned()->index('user_id');
            $table->string('ip', 20)->nullable()->index('ip');
            $table->text('ips', 65535)->nullable();
            $table->string('host', 128)->nullable();
            $table->string('user_agent', 191)->nullable();
            $table->text('meta', 65535)->nullable();
            $table->string('os', 70);
            $table->string('brand', 50);
            $table->string('device', 50);
            $table->string('browser', 60);
            $table->string('country_code', 50);
            $table->string('country', 100);
            $table->string('region', 100);
            $table->string('city', 100);
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
