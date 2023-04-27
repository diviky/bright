<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('bright.table.email_logs'), function (Blueprint $table): void {
            $table->char('id', 36)->primary();
            $table->string('from', 191)->nullable();
            $table->string('to', 191)->nullable();
            $table->string('cc', 191)->nullable();
            $table->string('bcc', 191)->nullable();
            $table->string('subject', 191);
            $table->text('body');
            $table->text('headers')->nullable();
            $table->text('attachments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop(config('bright.table.email_logs'));
    }
};
