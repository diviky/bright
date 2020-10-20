<?php

namespace Diviky\Bright\Database\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class Query implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 10;

    protected $sql      = [];
    protected $bindings = [];

    /**
     * Create a new job instance.
     *
     * @param mixed $options
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
