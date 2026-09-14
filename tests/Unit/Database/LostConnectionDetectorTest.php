<?php

declare(strict_types=1);

use Diviky\Bright\Database\LostConnectionDetector;

it('treats mysql commands out of sync as a lost connection', function () {
    $detector = new LostConnectionDetector;

    expect($detector->causedByLostConnection(new \Exception('Commands out of sync; you can\'t run this command now')))
        ->toBeTrue();
});

it('treats unbuffered query conflicts as a lost connection', function () {
    $detector = new LostConnectionDetector;

    expect($detector->causedByLostConnection(new \Exception('Cannot execute queries while other unbuffered queries are active')))
        ->toBeTrue();
});

it('delegates other messages to the framework detector', function () {
    $detector = new LostConnectionDetector;

    expect($detector->causedByLostConnection(new \Exception('server has gone away')))->toBeTrue()
        ->and($detector->causedByLostConnection(new \Exception('unrelated application error')))->toBeFalse();
});
