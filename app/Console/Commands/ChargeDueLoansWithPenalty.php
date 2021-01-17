<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\CronJobs;

class ChargeDueLoansWithPenalty extends Command
{
    use CronJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chargedueloanswithpenalty:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'bill users who have due loans';

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
     * @return int
     */
    public function handle()
    {
        $due_loans = $this->getAllDueLoans();
        return $this->chargeDueLoans($due_loans); 
    }
}
