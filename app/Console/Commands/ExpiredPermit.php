<?php

namespace App\Console\Commands;

use App\Models\Permit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpiredPermit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permit:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expired Permit';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $permit = Permit::where('created_at', '>', date('Y-m-d'))->get();
        Log::info($permit);
    }
}
