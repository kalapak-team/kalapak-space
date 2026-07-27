<?php

namespace App\Observers;

use App\Models\BlogPost;
use App\Observers\Concerns\InterceptsAdminActions;
use App\Support\PublicApiCache;

class BlogPostObserver
{
    use InterceptsAdminActions;

    protected string $resource = 'blog_posts';

    public function saved(BlogPost $post): void
    {
        PublicApiCache::forgetBlog();
        PublicApiCache::forgetBlogShow($post->slug);
        if ($post->wasChanged('slug')) {
            PublicApiCache::forgetBlogShow($post->getOriginal('slug'));
        }
    }

    public function deleted(BlogPost $post): void
    {
        PublicApiCache::forgetBlog();
        PublicApiCache::forgetBlogShow($post->slug);
    }
}
