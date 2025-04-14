<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectTrendingTweetsController extends Controller
{
    public function __invoke()
    {
        $projectId = \request()->route('project_id');

        return response()->json([
            'status' => 'ok'
        ]);
    }
}
