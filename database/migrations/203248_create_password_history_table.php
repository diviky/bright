<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('bright.table.password_history'), function (Blueprint $table): void {
            $table->increments('id');
            $table->foreignId('user_id')->index('user_id');
            $table->string('password', 191);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop(config('bright.table.password_history'));
    }
};
