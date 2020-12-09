<?php

namespace Diviky\Bright\Database\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class Statement implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    protected $sql      = [];
    protected $bindings = [];

    /**
     * Create a new job instance.
     *
     * @param mixed $sql
     * @param mixed $bindings
     */
    public function __construct($sql, $bindings = [])
    {
        $this->sql      = $sql;
        $this->bindings = $bindings;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        DB::statement($this->sql, $this->bindings);
    }
}
