<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::table($tableNames['permissions'], function (Blueprint $table): void {
            $table->string('display_name', 50)->nullable()->after('guard_name');
            $table->boolean('status')->default(0);
        });

        Schema::table($tableNames['roles'], function (Blueprint $table): void {
            $table->string('display_name', 50)->nullable()->after('guard_name');
        });

        Schema::table($tableNames['role_has_permissions'], function (Blueprint $table): void {
            $table->boolean('is_exclude')->default(0);
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table): void {
            $table->boolean('is_exclude')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::table($tableNames['permissions'], function (Blueprint $table): void {
            $table->dropColumn('display_name');
            $table->dropColumn('status');
        });

        Schema::table($tableNames['roles'], function (Blueprint $table): void {
            $table->dropColumn('display_name');
        });

        Schema::table($tableNames['role_has_permissions'], function (Blueprint $table): void {
            $table->dropColumn('is_exclude');
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table): void {
            $table->dropColumn('is_exclude');
        });
    }
};
