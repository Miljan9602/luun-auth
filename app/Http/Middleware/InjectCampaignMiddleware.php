<?php

namespace App\Http\Middleware;

use App\Repository\Campaign\CampaignRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectCampaignMiddleware
{

    protected CampaignRepositoryInterface $campaignRepository;

    /**
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(CampaignRepositoryInterface $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $campaignId = $request->route('campaign_id');

        if ($campaign = $this->campaignRepository->campaignById($campaignId)) {
            $request->merge(['campaign' => $campaign]);
            return $next($request);
        }

        return \response()->json([
            'status' => 'fail',
            'error' => [
                'code' => 404,
                'message' => 'Campaign with that id does not exist.',
                'type' => 'campaign_not_found'
            ]
        ], 404);

    }
}
