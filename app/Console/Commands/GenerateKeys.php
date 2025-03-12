<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;

class GenerateKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-keys';

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

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'rakitamiljan@yahoo.com'
        ]);

        $user = User::first();

        Passport::personalAccessTokensExpireIn(Carbon::now()->addDays(2));

        Auth::setUser($user);

        $token = Auth::user()->createToken('dswap')->accessToken;
    }
}
