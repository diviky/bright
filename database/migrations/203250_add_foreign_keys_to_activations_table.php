<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAuthActivationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(config('bright.table.activations'), function (Blueprint $table): void {
            $table->foreign('user_id', 'activations_user_id_foreign')->references('id')->on(config('bright.table.users'))->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('bright.table.activations'), function (Blueprint $table): void {
            $table->dropForeign('activations_user_id_foreign');
        });
    }
}
