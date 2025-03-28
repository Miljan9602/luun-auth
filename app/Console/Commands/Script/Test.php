<?php

namespace App\Console\Commands\Script;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result = DB::connection('mongodb')->table('products')->insert([
            'name' => 'Laptop',
            'price' => 1500,
            'stock' => 10
        ]);

        dd($result);
    }
}
