<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests\Database;

use Carbon\Carbon;
use Diviky\Bright\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    PlusMinusTestModel::$updatedEventCount = 0;

    Schema::create('test_plus_minus_counters', function ($table) {
        $table->id();
        $table->unsignedInteger('value')->default(0);
        $table->timestamps();
    });
});

it('plus updates value and leaves updated_at unchanged', function () {
    Carbon::setTestNow(Carbon::parse('2020-01-01 12:00:00'));

    $row = PlusMinusTestModel::create(['value' => 10]);
    $updatedAtAfterCreate = $row->updated_at->copy();

    Carbon::setTestNow(Carbon::parse('2025-06-01 15:00:00'));

    $result = $row->plus('value', 5);

    expect($result)->toBeInt()->toBe(1);

    $row->refresh();

    expect($row->value)->toBe(15);
    expect($row->updated_at->equalTo($updatedAtAfterCreate))->toBeTrue();
});

it('stock increment updates updated_at', function () {
    Carbon::setTestNow(Carbon::parse('2020-01-01 12:00:00'));

    $row = PlusMinusTestModel::create(['value' => 10]);
    $updatedAtAfterCreate = $row->updated_at->copy();

    Carbon::setTestNow(Carbon::parse('2025-06-01 15:00:00'));

    $row->increment('value', 3);
    $row->refresh();

    expect($row->value)->toBe(13);
    expect($row->updated_at->gt($updatedAtAfterCreate))->toBeTrue();
});

it('minus updates value and leaves updated_at unchanged', function () {
    Carbon::setTestNow(Carbon::parse('2020-01-01 12:00:00'));

    $row = PlusMinusTestModel::create(['value' => 20]);
    $updatedAtAfterCreate = $row->updated_at->copy();

    Carbon::setTestNow(Carbon::parse('2025-06-01 15:00:00'));

    $row->minus('value', 4);
    $row->refresh();

    expect($row->value)->toBe(16);
    expect($row->updated_at->equalTo($updatedAtAfterCreate))->toBeTrue();
});

it('builder plus updates value and leaves updated_at unchanged', function () {
    Carbon::setTestNow(Carbon::parse('2020-01-01 12:00:00'));

    $row = PlusMinusTestModel::create(['value' => 7]);
    $updatedAtAfterCreate = $row->updated_at->copy();

    Carbon::setTestNow(Carbon::parse('2025-06-01 15:00:00'));

    PlusMinusTestModel::query()->whereKey($row->getKey())->plus('value', 2);

    $row->refresh();

    expect($row->value)->toBe(9);
    expect($row->updated_at->equalTo($updatedAtAfterCreate))->toBeTrue();
});

it('builder minus updates value and leaves updated_at unchanged', function () {
    Carbon::setTestNow(Carbon::parse('2020-01-01 12:00:00'));

    $row = PlusMinusTestModel::create(['value' => 100]);
    $updatedAtAfterCreate = $row->updated_at->copy();

    Carbon::setTestNow(Carbon::parse('2025-06-01 15:00:00'));

    PlusMinusTestModel::query()->whereKey($row->getKey())->minus('value', 25);

    $row->refresh();

    expect($row->value)->toBe(75);
    expect($row->updated_at->equalTo($updatedAtAfterCreate))->toBeTrue();
});

it('plus fires updated observer', function () {
    Carbon::setTestNow(Carbon::parse('2020-01-01 12:00:00'));

    $row = PlusMinusTestModel::create(['value' => 1]);
    $before = PlusMinusTestModel::$updatedEventCount;

    $row->plus('value', 1);

    expect(PlusMinusTestModel::$updatedEventCount)->toBe($before + 1);
});

it('plusQuietly does not fire updated observer', function () {
    Carbon::setTestNow(Carbon::parse('2020-01-01 12:00:00'));

    $row = PlusMinusTestModel::create(['value' => 1]);
    $before = PlusMinusTestModel::$updatedEventCount;

    $row->plusQuietly('value', 1);

    expect(PlusMinusTestModel::$updatedEventCount)->toBe($before);
    $row->refresh();
    expect($row->value)->toBe(2);
});
