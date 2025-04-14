<?php

namespace App\Repository\Project;

use App\Collection\ProjectCollection;
use App\Models\Project;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function all(): ProjectCollection
    {
        return new ProjectCollection(Project::all());
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function projectById(string $twitterId): ?Project
    {
        return Project::where('twitter_id', $twitterId)->first();
    }
}
