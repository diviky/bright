<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Sql;

final class StringExpression
{
    /**
     * Flatten a text column for CSV export (escape quotes, strip line breaks).
     */
    public static function flattenForCsvExport(string $qualifiedColumn, string $driver): string
    {
        $source = match ($driver) {
            'pgsql' => "({$qualifiedColumn})::text",
            default => $qualifiedColumn,
        };

        $newline = self::characterFunction($driver, 10);
        $tab = self::characterFunction($driver, 9);
        $carriageReturn = self::characterFunction($driver, 13);

        return "REPLACE(REPLACE(REPLACE(REPLACE({$source}, '\"', '\"\"'), {$newline}, ' '), {$tab}, ' '), {$carriageReturn}, ' ')";
    }

    private static function characterFunction(string $driver, int $code): string
    {
        return match ($driver) {
            'pgsql' => "CHR({$code})",
            'mysql', 'mariadb' => "CHAR({$code})",
            default => "char({$code})",
        };
    }
}
