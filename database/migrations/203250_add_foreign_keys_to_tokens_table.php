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
        Schema::table(config('bright.table.tokens'), function (Blueprint $table): void {
            $table->foreign('user_id', 'tokens_user_id_foreign')->references('id')->on(config('bright.table.users'))->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('bright.table.tokens'), function (Blueprint $table): void {
            $table->dropForeign('tokens_user_id_foreign');
        });
    }
};
