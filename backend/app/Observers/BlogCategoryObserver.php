<?php

namespace App\Observers;

use App\Models\BlogCategory;
use App\Observers\Concerns\InterceptsAdminActions;
use App\Support\PublicApiCache;

class BlogCategoryObserver
{
    use InterceptsAdminActions;

    protected string $resource = 'categories';

    public function saved(BlogCategory $category): void
    {
        PublicApiCache::forgetBlog();
    }

    public function deleted(BlogCategory $category): void
    {
        PublicApiCache::forgetBlog();
    }
}
