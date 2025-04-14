<?php

namespace App\Http\Controllers\Relationship;

use App\Http\Controllers\Controller;
use App\Repository\Follow\FollowRepositoryInterface;
use Illuminate\Http\Request;

class FollowersController extends Controller
{
    public function __invoke(Request $request, FollowRepositoryInterface $followRepository)
    {

    }
}
