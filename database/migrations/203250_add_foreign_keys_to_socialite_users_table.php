<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('bright.table.socialite_users'), function (Blueprint $table): void {
            $table->foreign('user_id', 'socialite_users_ibfk_1')->references('id')->on(config('bright.table.users'))->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('bright.table.socialite_users'), function (Blueprint $table): void {
            $table->dropForeign('socialite_users_ibfk_1');
        });
    }
};
