<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Redis-backed cache keys for public read APIs (reduces Prisma/Postgres operations).
 */
final class PublicApiCache
{
    public const KEY_TEAM = 'public.team';

    public const KEY_TAGS = 'public.tags';

    public const KEY_SETTINGS = 'public.settings';

    public const KEY_BLOG_CATEGORIES = 'public.blog.categories';

    public const KEY_DOCS_NAV = 'docs.nav';

    public const KEY_DOCS_INDEX = 'docs.index';

    public static function ttl(): int
    {
        return max(60, (int) env('PUBLIC_API_CACHE_TTL', 3600));
    }

    public static function version(string $resource): int
    {
        return (int) Cache::get("public.{$resource}.cache_version", 1);
    }

    public static function bump(string $resource): void
    {
        $key = "public.{$resource}.cache_version";

        if (! Cache::has($key)) {
            Cache::put($key, 2, 60 * 60 * 24 * 30);

            return;
        }

        Cache::increment($key);
    }

    /**
     * @param  array<string, mixed>  $queryParams
     */
    public static function listKey(string $resource, array $queryParams): string
    {
        ksort($queryParams);
        $version = self::version($resource);
        $hash = hash('xxh128', json_encode($queryParams, JSON_THROW_ON_ERROR));

        return "public.{$resource}.v{$version}.{$hash}";
    }

    public static function blogShowKey(string $slug): string
    {
        return 'public.blog.show.' . $slug;
    }

    public static function projectShowKey(string $slug): string
    {
        return 'public.projects.show.' . $slug;
    }

    public static function docShowKey(string $slug): string
    {
        return 'docs.show.' . $slug;
    }

    public static function forgetBlog(): void
    {
        self::bump('blog');
        Cache::forget(self::KEY_BLOG_CATEGORIES);
    }

    public static function forgetBlogShow(?string $slug): void
    {
        if ($slug) {
            Cache::forget(self::blogShowKey($slug));
        }
    }

    public static function forgetProjects(): void
    {
        self::bump('projects');
    }

    public static function forgetProjectShow(?string $slug): void
    {
        if ($slug) {
            Cache::forget(self::projectShowKey($slug));
        }
    }

    public static function forgetTeam(): void
    {
        Cache::forget(self::KEY_TEAM);
    }

    public static function forgetTags(): void
    {
        Cache::forget(self::KEY_TAGS);
    }

    public static function forgetSettings(): void
    {
        Cache::forget(self::KEY_SETTINGS);
    }

    public static function forgetDocs(): void
    {
        Cache::forget(self::KEY_DOCS_NAV);
        Cache::forget(self::KEY_DOCS_INDEX);
    }

    public static function forgetDocShow(?string $slug): void
    {
        if ($slug) {
            Cache::forget(self::docShowKey($slug));
        }
    }
}
