<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class CreateProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-project';

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
        $seiEcosystem = Project::create([
            'twitter_name' => 'Sei 🔴',
            'twitter_username' => 'SeiNetwork',
            'twitter_id' => '1515104342906327045',
            'type' => 'ecosystem',
            'website' => 'https://www.sei.io/',
            'socials' => json_encode(['x' => 'https://x.com/SeiNetwork']),
            'ticker' => 'SEI',
            'logo_url' => 'https://pbs.twimg.com/profile_images/1873839225914716160/w8650_qp_normal.jpg',
            'description' => 'The Fastest Layer 1. Parallelizing the EVM // Join the Sei community: https://t.co/zYasNBD1kr RT ≠ endorsement. Account managed by the @Sei_FND',
        ]);

        Project::create([
            'twitter_name' => 'DragonSwap',
            'twitter_username' => 'dragonswap_dex',
            'twitter_id' => '1742150897658982400',
            'type' => 'project',
            'website' => 'https://dragonswap.app/',
            'socials' => json_encode(['x' => 'https://x.com/dragonswap_dex']),
            'ticker' => 'DSWAP',
            'logo_url' => 'https://pbs.twimg.com/profile_images/1877416898993770496/uA0AA25O_normal.jpg',
            'description' => 'Native DeFi Hub on @SeiNetwork',
            'ecosystem_id' => $seiEcosystem->twitter_id,
            'is_featured_enabled' => true,
            'featured_image_url' => 'https://s3-alpha-sig.figma.com/img/a365/f213/2f688e0968903596d1ae218d0bd0a721?Expires=1745798400&Key-Pair-Id=APKAQ4GOSFWCW27IBOMQ&Signature=gByYxCP3j7nNdKQVO~QvuoGzGeEcJO5zeczDiGYM4MVG1U2SXkOYy2bw7HrNH4o9Jb1jOFtRXDikAy8gbPErQill-I~5I~NNZDXlkKJrp3DY2ePDR84Ad-qWBpQmz7L97g7JdM8oF87e~9aPdQ-TsLydFVzuwNpdZpTaY~Ueh9LS0O60l-LNy1SsYJw797vVaYXo0gh4StIc49AVk199A5vbetbnT8sY8AK-anqnCT4TH2OjFeqw8rW3pJxRbaTVUbJPbY9KG-biygj6Ihq-pCH84gocl9YEQad5O-TljjyG84sTIqKsbxx65-Rk7jDv9jkxfvLjYbw0u6cbz0th9Q__'
        ]);

        Project::create([
            'twitter_name' => 'DSX',
            'twitter_username' => 'DSX_app',
            'twitter_id' => '1848426314979520512',
            'type' => 'multichain',
            'website' => 'https://dsx.app/',
            'socials' => json_encode(['x' => 'https://x.com/DSX_app']),
            'ticker' => 'DSX',
            'logo_url' => 'https://pbs.twimg.com/profile_images/1848426558458867712/hAeVjj-C_normal.jpg',
            'description' => 'Every Chain, On Chain, One App: Powered by @dragonswap_dex',
        ]);
    }
}
