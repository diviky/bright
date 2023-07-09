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
        Schema::create(config('bright.table.tokens'), function (Blueprint $table): void {
            $table->increments('id');
            $table->morphs('tokenable');
            $table->string('name');
            $table->foreignId('user_id')->index('tokens_user_id_foreign');
            $table->string('access_token', 100)->unique('access_token');
            $table->string('refresh_token', 100)->nullable();
            $table->text('abilities')->nullable();
            $table->text('allowed_ip')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
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
