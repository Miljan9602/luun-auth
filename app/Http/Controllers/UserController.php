<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function me()
    {
        return response()->json([
            'status' => 'ok',
            'user' => new UserResource(auth()->user())
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function updateWallet()
    {
        $wallet = Arr::get(request()->all(), 'wallet');

        Auth::user()->update(['evm_wallet' => $wallet]);

        return response()->json(['status' => 'ok']);
    }
}
