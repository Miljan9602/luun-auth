<?php

namespace App\Http\Middleware;

use App\Repository\Project\ProjectRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectProjectMiddleware
{

    protected ProjectRepositoryInterface $projectRepository;

    /**
     * @param ProjectRepositoryInterface $projectRepository
     */
    public function __construct(ProjectRepositoryInterface $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $projectId = $request->route('project_id');

        if ($project = $this->projectRepository->projectById($projectId)) {
            $request->merge(['project' => $project]);
            return $next($request);
        }

        return \response()->json([
            'status' => 'fail',
            'error' => [
                'code' => 404,
                'message' => 'Project with that id does not exist.',
                'type' => 'project_not_found'
            ]
        ], 404);
    }
}
