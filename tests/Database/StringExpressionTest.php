<?php

declare(strict_types=1);

use Diviky\Bright\Database\Sql\StringExpression;

it('builds postgres csv flatten sql with text cast and chr', function (): void {
    $sql = StringExpression::flattenForCsvExport('m.payload', 'pgsql');

    expect($sql)->toContain('(m.payload)::text')
        ->and($sql)->toContain('CHR(10)')
        ->and($sql)->toContain('CHR(9)')
        ->and($sql)->toContain('CHR(13)');
});

it('builds mysql csv flatten sql with char', function (): void {
    $sql = StringExpression::flattenForCsvExport('m.payload', 'mysql');

    expect($sql)->toContain('m.payload')
        ->and($sql)->not->toContain('::text')
        ->and($sql)->toContain('CHAR(10)');
});
