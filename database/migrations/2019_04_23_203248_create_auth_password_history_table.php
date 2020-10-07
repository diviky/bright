<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthPasswordHistoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('karla.table.password_history'), function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('user_id')->index('user_id');
            $table->string('password', 191);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('karla.table.password_history'));
    }
}
