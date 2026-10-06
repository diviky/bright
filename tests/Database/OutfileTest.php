<?php

declare(strict_types=1);

use Diviky\Bright\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(TestCase::class);

beforeEach(function (): void {
    Schema::create('outfile_rows', function ($table): void {
        $table->id();
        $table->string('name');
        $table->unsignedInteger('amount');
    });

    DB::table('outfile_rows')->insert([
        ['name' => 'alpha', 'amount' => 1],
        ['name' => 'beta', 'amount' => 2],
    ]);
});

afterEach(function (): void {
    Schema::dropIfExists('outfile_rows');
});

it('exports query results to csv without mysql into outfile on sqlite', function (): void {
    $path = sys_get_temp_dir() . '/bright-outfile-' . uniqid('', true) . '.csv';

    try {
        $written = DB::table('outfile_rows')
            ->select(['name', 'amount'])
            ->orderBy('id')
            ->out($path);

        expect($written)->toBe($path)
            ->and(is_file($path))->toBeTrue();

        $lines = array_values(array_filter(explode("\n", trim((string) file_get_contents($path)))));

        expect($lines)->toHaveCount(2)
            ->and($lines[0])->toBe('alpha,1')
            ->and($lines[1])->toBe('beta,2');
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});
