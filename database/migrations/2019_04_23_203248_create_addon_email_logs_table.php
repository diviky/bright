<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAddonEmailLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(config('bright.table.email_logs'), function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('from', 191)->nullable();
            $table->string('to', 191)->nullable();
            $table->string('cc', 191)->nullable();
            $table->string('bcc', 191)->nullable();
            $table->string('subject', 191);
            $table->text('body', 65535);
            $table->text('headers', 65535)->nullable();
            $table->text('attachments', 65535)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop(config('bright.table.email_logs'));
    }
}
