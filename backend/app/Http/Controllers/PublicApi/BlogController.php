<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogPostResource;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Support\PublicApiCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::with(['author', 'category', 'series'])
            ->withCount('approvedComments')
            ->where('status', 'published');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('excerpt', 'ilike', "%{$search}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $category));
        }

        // Prefer author_id (stable FK). If author_id is present but invalid (e.g. 0), fall back to ?author=username.
        $authorScoped = false;
        if ($request->filled('author_id')) {
            $authorId = (int) $request->get('author_id');
            if ($authorId > 0) {
                $query->where('author_id', $authorId)
                    ->whereHas('author', fn($q) => $q->where('is_active', true));
                $authorScoped = true;
            }
        }
        if (!$authorScoped) {
            $author = strtolower(trim((string) $request->get('author')));
            if ($author !== '') {
                $query->whereHas('author', fn($q) => $q->whereRaw('LOWER(username) = ?', [$author])->where('is_active', true));
            }
        }

        $perPage = min(50, max(1, (int) $request->get('per_page', 9)));
        $cacheKey = PublicApiCache::listKey('blog', $request->query());

        $payload = Cache::remember($cacheKey, PublicApiCache::ttl(), function () use ($query, $perPage) {
            $posts = (clone $query)->orderByRaw('COALESCE(published_at, created_at) DESC')->paginate($perPage);

            return [
                'success' => true,
                'data' => BlogPostResource::collection($posts)->resolve(),
                'meta' => [
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                ],
            ];
        });

        return response()->json($payload);
    }

    public function show(string $slug): JsonResponse
    {
        $post = Cache::remember(
            PublicApiCache::blogShowKey($slug),
            PublicApiCache::ttl(),
            function () use ($slug) {
                return BlogPost::with(['author', 'category', 'series', 'approvedComments.user', 'approvedComments.replies'])
                    ->where('slug', $slug)
                    ->where('status', 'published')
                    ->firstOrFail();
            }
        );

        $post->increment('views_count');

        return response()->json([
            'success' => true,
            'data' => new BlogPostResource($post),
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Cache::remember(PublicApiCache::KEY_BLOG_CATEGORIES, PublicApiCache::ttl(), function () {
            return BlogCategory::withCount(['posts' => fn ($q) => $q->where('status', 'published')])->get();
        });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
