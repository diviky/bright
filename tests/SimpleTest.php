<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests;

test('basic test works', function () {
    expect(true)->toBe(true);
});

test('can do math', function () {
    expect(2 + 2)->toBe(4);
});

test('laravel app is available', function () {
    expect(app())->not->toBeNull();
});

test('can access app services', function () {
    $app = app();
    expect(method_exists($app, 'make'))->toBeTrue();
    expect(method_exists($app, 'bound'))->toBeTrue();
});

test('can check if services are bound', function () {
    $app = app();
    expect($app->bound('config'))->toBeFalse();
    expect($app->bound('db'))->toBeFalse();
});
