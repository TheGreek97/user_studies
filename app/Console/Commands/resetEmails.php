<?php

namespace App\Console\Commands;

use Database\Seeders\EmailSeeder;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class resetEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resetEmails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('emails')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            (new \Database\Seeders\EmailSeeder)->run();
            echo ('Emails table truncated and re-seeded'."\n");
        } catch (QueryException $e){
            echo ($e);
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
