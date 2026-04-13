<?php

namespace App\Observers;

use App\Observers\Concerns\InterceptsAdminActions;

class BlogPostObserver
{
    use InterceptsAdminActions;
    protected string $resource = 'blog_posts';
}
