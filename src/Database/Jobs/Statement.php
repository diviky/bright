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
     * @var string
     */
    protected $bindings;

    /**
     * Change version.
     *
     * @var int
     */
    protected $version = 1;

    /**
     * Create a new job instance.
     *
     * @param  string  $sql
     * @param  string  $bindings
     * @param  mixed  $version
     */
    public function __construct($sql, $bindings, $version = 1)
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
        $this->version = $version;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->version == 2) {
            DB::statement($this->sql, (array) unserialize(base64_decode($this->bindings)));

            return;
        }

        DB::statement($this->sql, (array) unserialize($this->bindings));
    }
}
