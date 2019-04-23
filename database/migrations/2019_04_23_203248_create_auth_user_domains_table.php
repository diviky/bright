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
        Schema::create('auth_user_domains', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->integer('user_id')->unsigned();
            $table->integer('domain_id')->unsigned();
            $table->unique(['user_id', 'domain_id'], 'user_domain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('auth_user_domains');
    }
}
