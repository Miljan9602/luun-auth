<?php

namespace App\Http\Controllers\Campaign;

use App\Action\Campaign\VerifyTaskAction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class VerifyTaskController extends Controller
{
    public function __invoke(VerifyTaskAction $action)
    {
        $campaign = request()->campaign;
        $user = Auth::user();

        return response()->json([
            'status' => $action->handle($campaign, $user) === true ? 'ok' : 'false'
        ]);
    }
}
