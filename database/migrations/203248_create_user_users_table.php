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
        Schema::create(config('bright.table.user_users'), function (Blueprint $table): void {
            $table->foreignId('parent_id');
            $table->foreignId('user_id');
            $table->unique(['parent_id', 'user_id'], 'parent_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop(config('bright.table.user_users'));
    }
};
