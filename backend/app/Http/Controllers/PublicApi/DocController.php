<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Models\Doc;
use Illuminate\Http\JsonResponse;

class DocController extends Controller
{
    public function index(): JsonResponse
    {
        // Return top-level pages with their published subpages, grouped by category
        $docs = Doc::where('status', 'published')
            ->whereNull('parent_id')
            ->with([
                'children' => fn($q) => $q
                    ->where('status', 'published')
                    ->select('id', 'title', 'slug', 'parent_id', 'order_num', 'updated_at')
                    ->orderBy('order_num')
            ])
            ->select('id', 'title', 'slug', 'category', 'order_num', 'parent_id', 'updated_at')
            ->orderBy('category')
            ->orderBy('order_num')
            ->get()
            ->groupBy('category');

        return response()->json(['success' => true, 'data' => $docs]);
    }

    public function show(string $slug): JsonResponse
    {
        $doc = Doc::where('slug', $slug)
            ->where('status', 'published')
            ->with(['sections' => fn($q) => $q->orderBy('order_num')])
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $doc]);
    }
}
