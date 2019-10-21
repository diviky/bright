<?php

namespace Karla\Database\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Query implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
