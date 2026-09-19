<?php

declare(strict_types=1);

use Diviky\Bright\Database\Octane\MySqlStringBindingConnection;

it('exposes bright connection helpers on the octane mysql connection', function () {
    if (! class_exists(\Laravel\Octane\Swoole\Database\MySqlStringBindingConnection::class)) {
        $this->markTestSkipped('Octane is not installed.');
    }

    $connection = new MySqlStringBindingConnection(new stdClass, 'app', '', [
        'driver' => 'mysql',
        'bright' => [],
    ]);

    expect(method_exists($connection, 'sync'))->toBeTrue();
    expect(method_exists($connection, 'async'))->toBeTrue();
    expect(method_exists($connection, 'resetRequestState'))->toBeTrue();

    $connection->sync();

    expect(true)->toBeTrue();
});
