<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Repository\Project\ProjectRepositoryInterface;

class ShowAllProjectsController extends Controller
{
    public function __invoke(ProjectRepositoryInterface $projectRepository)
    {
        return response()->json([
            'status' => 'ok',
            'projects' => ProjectResource::collection($projectRepository->all())
        ]);
    }
}
