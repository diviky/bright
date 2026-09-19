<?php

declare(strict_types=1);

use Diviky\Bright\Database\QueryGrammarConfigurator;
use Illuminate\Database\MySqlConnection;

uses()->group('database');

it('applies bright config to extended mysql connections without setConfig on the stock grammar', function () {
    $connection = new MySqlConnection(new stdClass, 'app', '', [
        'driver' => 'mysql',
        'bright' => [
            'databases' => [
                'names' => ['users' => 'shared_db'],
            ],
        ],
    ]);

    expect(method_exists($connection->getQueryGrammar(), 'setConfig'))->toBeFalse();

    QueryGrammarConfigurator::apply($connection, $connection->getConfig()['bright']);

    expect(method_exists($connection->getQueryGrammar(), 'setConfig'))->toBeTrue();

    $wrapped = $connection->getQueryGrammar()->wrapTable('users');

    expect($wrapped)->toContain('shared_db');
});

it('merges bright config when the grammar already supports setConfig', function () {
    $connection = new Diviky\Bright\Database\MySqlConnection(new stdClass, 'app', '', [
        'driver' => 'mysql',
        'bright' => [
            'databases' => [
                'names' => ['posts' => 'blog_db'],
            ],
        ],
    ]);

    QueryGrammarConfigurator::apply($connection, $connection->getConfig()['bright']);

    $wrapped = $connection->getQueryGrammar()->wrapTable('posts');

    expect($wrapped)->toContain('blog_db');
});
