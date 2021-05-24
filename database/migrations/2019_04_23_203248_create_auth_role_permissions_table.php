<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuthRolePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('bright.table.role_permissions'), function (Blueprint $table): void {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned()->index('role_permissions_role_id_foreign');
            $table->boolean('is_exclude')->default(0);
            $table->primary(['permission_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop(config('bright.table.role_permissions'));
    }
}
