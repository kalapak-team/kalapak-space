<?php

namespace App\Observers;

use App\Models\Tag;
use App\Observers\Concerns\InterceptsAdminActions;
use App\Support\PublicApiCache;

class TagObserver
{
    use InterceptsAdminActions;

    protected string $resource = 'tags';

    public function saved(Tag $tag): void
    {
        PublicApiCache::forgetTags();
        PublicApiCache::forgetProjects();
    }

    public function deleted(Tag $tag): void
    {
        PublicApiCache::forgetTags();
        PublicApiCache::forgetProjects();
    }
}
