<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class Statement implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    /**
     * Sql statement.
     *
     * @var string
     */
    protected $sql;

    /**
     * Statment bindings.
     *
     * @var array|string
     */
    protected $bindings = [];

    /**
     * Create a new job instance.
     *
     * @param string $sql
     * @param array  $bindings
     * @param mixed  $serialized
     */
    public function __construct($sql, $bindings = [])
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (is_array($this->bindings)) {
            DB::statement($this->sql, $this->bindings);
        } else {
            DB::statement($this->sql, (array) unserialize($this->bindings));
        }
    }
}
