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
        Schema::table(config('bright.table.password_history'), function (Blueprint $table): void {
            $table->foreign('user_id', 'password_history')->references('id')->on(config('bright.table.users'))->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('bright.table.password_history'), function (Blueprint $table): void {
            $table->dropForeign('password_history');
        });
    }
};
