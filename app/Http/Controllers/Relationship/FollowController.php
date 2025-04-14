<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;
use App\Repository\Follow\FollowRepositoryInterface;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __invoke(Request $request, FollowRepositoryInterface $followRepository)
    {
        $loggedInUser = auth()->user();

        $loggedInUserId = $loggedInUser->getId();
        $userToFollow = $request->route('user_id');

        if (!$followRepository->hasFollow($loggedInUserId, $userToFollow)) {
            $followRepository->follow($loggedInUserId, $userToFollow);
        }

        return response()->json(['status' => 'ok']);
    }
}
