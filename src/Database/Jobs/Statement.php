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
     * @var array
     */
    protected $bindings = [];

    /**
     * Create a new job instance.
     *
     * @param string $sql
     * @param array  $bindings
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
        DB::statement($this->sql, $this->bindings);
    }
}
