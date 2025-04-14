<?php

namespace App\Repository\Project;

use App\Collection\ProjectCollection;
use App\Models\Project;

interface ProjectRepositoryInterface
{
    /**
     * @return ProjectCollection
     */
    public function all() : ProjectCollection;

    /**
     * @param array $data
     * @return Project
     */
    public function create(array $data) : Project;

    /**
     * @param string $twitterId
     * @return Project|null
     */
    public function projectById(string $twitterId) : ?Project;
}
