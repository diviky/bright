<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('bright.table.user_domains'), function (Blueprint $table): void {
            $table->id('id');
            $table->foreignId('owner_id');
            $table->foreignId('domain_id');
            $table->unique(['owner_id', 'domain_id'], 'user_domain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop(config('bright.table.user_domains'));
    }
};
