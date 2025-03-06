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
        Schema::create(config('bright.table.tokens'), function (Blueprint $table): void {
            $table->increments('id');
            $table->morphs('tokenable');
            $table->string('name')->nullable();
            $table->text('abilities')->nullable();
            $table->string('token')->unique('tokens_access_token');
            $table->string('access_token')->unique('tokens_access_token');
            $table->string('refresh_token')->nullable();
            $table->text('allowed_ip')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->boolean('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop(config('bright.table.tokens'));
    }
};
