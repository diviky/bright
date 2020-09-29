<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthUserDomainsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('karla.table.user_domains'), function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('user_id');
            $table->foreignId('domain_id');
            $table->unique(['user_id', 'domain_id'], 'user_domain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('karla.table.user_domains'));
    }
}
