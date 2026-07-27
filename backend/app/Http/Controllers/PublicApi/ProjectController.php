<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Support\PublicApiCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::with(['tags', 'creator', 'collection'])->whereNull('deleted_at');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($tag = $request->get('tag')) {
            $query->whereHas('tags', fn($q) => $q->where('slug', $tag));
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $creatorScoped = false;
        if ($request->filled('created_by')) {
            $userId = (int) $request->get('created_by');
            if ($userId > 0) {
                $query->where('created_by', $userId)
                    ->whereHas('creator', fn($q) => $q->where('is_active', true));
                $creatorScoped = true;
            }
        }
        if (!$creatorScoped) {
            $creator = strtolower(trim((string) $request->get('creator')));
            if ($creator !== '') {
                $query->whereHas('creator', fn($q) => $q->whereRaw('LOWER(username) = ?', [$creator])->where('is_active', true));
            }
        }

        $sort = $request->get('sort', 'newest');
        $query = match ($sort) {
            'stars' => $query->orderByDesc('stars_count'),
            'name' => $query->orderBy('title'),
            default => $query->orderByDesc('created_at'),
        };

        $perPage = min(50, max(1, (int) $request->get('per_page', 12)));
        $cacheKey = PublicApiCache::listKey('projects', $request->query());

        $payload = Cache::remember($cacheKey, PublicApiCache::ttl(), function () use ($query, $perPage) {
            $projects = (clone $query)->paginate($perPage);

            return [
                'success' => true,
                'data' => ProjectResource::collection($projects)->resolve(),
                'meta' => [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                ],
            ];
        });

        return response()->json($payload);
    }

    public function show(string $slug): JsonResponse
    {
        $payload = Cache::remember(PublicApiCache::projectShowKey($slug), PublicApiCache::ttl(), function () use ($slug) {
            $project = Project::with(['tags', 'creator', 'collection'])->where('slug', $slug)->firstOrFail();

            return [
                'success' => true,
                'data' => (new ProjectResource($project))->resolve(),
            ];
        });

        return response()->json($payload);
    }
}
