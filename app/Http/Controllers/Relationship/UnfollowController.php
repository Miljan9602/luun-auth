<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;
use App\Repository\Follow\FollowRepositoryInterface;
use Illuminate\Http\Request;

class UnfollowController extends Controller
{
    public function __invoke(Request $request, FollowRepositoryInterface $followRepository)
    {
        $loggedInUser = auth()->user();

        $loggedInUserId = $loggedInUser->getId();
        $userToFollow = $request->route('user_id');

        $followRepository->unfollow($loggedInUserId, $userToFollow);

        return response()->json(['status' => 'ok']);
    }
}
