<?php

namespace App\Collection;

use Illuminate\Support\Collection;

class ProjectCollection extends Collection
{
    public function filterByType(string $type) : ProjectCollection
    {
        return $this->filter(function ($project) use ($type) {
            return $project->type === $type;
        });
    }
}
