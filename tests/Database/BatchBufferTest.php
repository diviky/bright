<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests\Database;

use Diviky\Bright\Database\BatchBuffer\BucketKey;
use Diviky\Bright\Database\BatchBuffer\InsertBatcher;
use Diviky\Bright\Database\BatchBuffer\RedisBatchBuffer;
use Diviky\Bright\Database\BatchBuffer\UpdateBatcher;
use Diviky\Bright\Database\Events\QueryQueued;
use Diviky\Bright\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Mockery;

class BatchBufferTest extends TestCase
{
    use RefreshDatabase;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('batch_items', function ($table): void {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('status')->nullable();
        });
    }

    public function test_bucket_key_make_and_parse(): void
    {
        $key = BucketKey::make('bright:batch', 'sqlite', 'users', 'insert');

        $this->assertSame('bright:batch:sqlite:users:insert', $key);

        $parsed = BucketKey::parse($key);

        $this->assertSame([
            'prefix' => 'bright:batch',
            'connection' => 'sqlite',
            'table' => 'users',
            'type' => 'insert',
        ], $parsed);
    }

    public function test_insert_batcher_merges_rows_and_inserts(): void
    {
        $batcher = new InsertBatcher;

        $count = $batcher->execute('sqlite', 'batch_items', [
            ['rows' => [['name' => 'a']]],
            ['rows' => [['name' => 'b', 'status' => 'new']]],
        ]);

        $this->assertSame(2, $count);
        $this->assertSame(2, DB::table('batch_items')->count());
    }

    public function test_update_batcher_uses_case_when_for_simple_id_wheres(): void
    {
        DB::table('batch_items')->insert([
            ['name' => 'a', 'status' => 'x'],
            ['name' => 'b', 'status' => 'x'],
        ]);

        $batcher = new UpdateBatcher;

        $count = $batcher->execute('sqlite', 'batch_items', [
            [
                'values' => ['status' => 'one'],
                'where_column' => 'id',
                'where_value' => 1,
            ],
            [
                'values' => ['status' => 'two'],
                'where_column' => 'id',
                'where_value' => 2,
            ],
        ]);

        $this->assertSame(2, $count);
        $this->assertSame('one', DB::table('batch_items')->where('id', 1)->value('status'));
    }

    public function test_batch_async_insert_pushes_to_buffer_and_skips_query_queued(): void
    {
        config()->set('bright.async.enable', true);
        config()->set('bright.async.batch.processor', 'command');

        $mock = Mockery::mock(RedisBatchBuffer::class);
        $mock->shouldReceive('pushRow')
            ->once()
            ->withArgs(function ($table, $type, $payload, $connection, $async) {
                return $table === 'batch_items'
                    && $type === 'insert'
                    && isset($payload['rows'][0]['name'])
                    && $connection === 'sqlite'
                    && is_array($async) && !empty($async[3]);
            })
            ->andReturn(true);

        app()->instance(RedisBatchBuffer::class, $mock);

        Event::fake([QueryQueued::class]);

        $result = DB::table('batch_items')
            ->async(null, null, null, true)
            ->insert(['name' => 'queued']);

        $this->assertTrue($result);
        Event::assertNotDispatched(QueryQueued::class);
    }
}
