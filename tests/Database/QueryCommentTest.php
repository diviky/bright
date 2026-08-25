<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests\Database;

use Diviky\Bright\Database\Eloquent\Model as BrightModel;
use Diviky\Bright\Database\Query\Grammars\MongoDBGrammar;
use Diviky\Bright\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Schema::create('test_comment_users', function ($table) {
        $table->id();
        $table->string('email');
        $table->boolean('active')->default(false);
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('test_comment_users');
});

it('prepends comment to select sql', function () {
    $sql = DB::table('test_comment_users')
        ->comment('fetch-active-users')
        ->where('active', 1)
        ->toSql();

    expect($sql)->toStartWith('/* fetch-active-users */');
    expect($sql)->toContain('select');
});

it('joins multiple comments in one block', function () {
    $sql = DB::table('test_comment_users')
        ->comment('report:daily-orders')
        ->comment('tenant:123')
        ->where('active', 1)
        ->toSql();

    expect($sql)->toStartWith('/* report:daily-orders tenant:123 */');
});

it('sanitizes dangerous comment characters', function () {
    $sql = DB::table('test_comment_users')
        ->comment('foo*/DROP')
        ->toSql();

    expect($sql)->toStartWith('/* fooDROP */');
    expect($sql)->not->toContain('*/DROP');
});

it('includes comment in toRawSql', function () {
    $rawSql = DB::table('test_comment_users')
        ->comment('auth:login-check')
        ->where('email', 'test@example.com')
        ->toRawSql();

    expect($rawSql)->toStartWith('/* auth:login-check */');
});

it('clears comments with withoutComments', function () {
    $sql = DB::table('test_comment_users')
        ->comment('temporary')
        ->withoutComments()
        ->toSql();

    expect($sql)->not->toStartWith('/*');
});

it('prepends comment to update and delete sql', function () {
    $updateQuery = DB::table('test_comment_users')
        ->comment('bulk-deactivate')
        ->where('active', 1);

    $updateSql = $updateQuery->getGrammar()->compileUpdate($updateQuery, ['active' => 0]);

    expect($updateSql)->toStartWith('/* bulk-deactivate */');
    expect($updateSql)->toContain('update');

    $deleteQuery = DB::table('test_comment_users')
        ->comment('cleanup')
        ->where('active', 0);

    $deleteSql = $deleteQuery->getGrammar()->compileDelete($deleteQuery);

    expect($deleteSql)->toStartWith('/* cleanup */');
    expect($deleteSql)->toContain('delete');
});

it('forwards comment through eloquent builder', function () {
    $model = new class extends BrightModel
    {
        protected $table = 'test_comment_users';
    };

    $sql = $model->newQuery()
        ->comment('eloquent-test')
        ->where('active', 1)
        ->toSql();

    expect($sql)->toStartWith('/* eloquent-test */');
});

it('noop comments for mongodb grammar', function () {
    $connection = DB::connection();
    $grammar = new MongoDBGrammar($connection);

    $builder = DB::table('test_comment_users')->comment('ignored');

    expect($grammar->compileQueryComments($builder))->toBe('');
});

it('stores comments on query builder', function () {
    $builder = DB::table('test_comment_users')->comment('store-test');

    expect($builder->getQueryComments())->toBe(['store-test']);
});
