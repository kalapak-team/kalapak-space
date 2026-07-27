<?php

namespace App\Observers;

use App\Models\Project;
use App\Observers\Concerns\InterceptsAdminActions;
use App\Support\PublicApiCache;

class ProjectObserver
{
    use InterceptsAdminActions;

    protected string $resource = 'projects';

    public function saved(Project $project): void
    {
        PublicApiCache::forgetProjects();
        PublicApiCache::forgetProjectShow($project->slug);
        if ($project->wasChanged('slug')) {
            PublicApiCache::forgetProjectShow($project->getOriginal('slug'));
        }
    }

    public function deleted(Project $project): void
    {
        PublicApiCache::forgetProjects();
        PublicApiCache::forgetProjectShow($project->slug);
    }
}
