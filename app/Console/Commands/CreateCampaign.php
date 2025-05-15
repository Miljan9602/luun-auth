<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

class CreateCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-campaign';

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

        Campaign::create([
            'project_twitter_id' => '1742150897658982400',
            'campaign_name' => 'Make A Swap!',
            'description' => 'User needs to have atleast one swap on dragonswap app that is worth more than 10$',
            'resolve_url' => 'https://sei-api.dragonswap.app/api/v1/okx',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonth(),
            'reward_usd' => 10000,
            'campaign_uuid' => Uuid::uuid4()->toString(),
        ]);
    }
}
