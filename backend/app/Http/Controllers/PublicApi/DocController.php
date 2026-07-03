<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Models\Doc;
use App\Models\DocMenu;
use Illuminate\Http\JsonResponse;

class DocController extends Controller
{
    /**
     * Navigation tree for the public docs sidebar.
     * Structure: Main Menu → Sub-Menus → Pages → Subpages
     */
    public function nav(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->loadNavTree()]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, DocMenu>
     */
    private function loadNavTree()
    {
        $publishedPages = Doc::where('status', 'published')
            ->whereNull('parent_id')
            ->with([
                'children' => fn($q) => $q
                    ->where('status', 'published')
                    ->select('id', 'title', 'slug', 'parent_id', 'order_num', 'updated_at')
                    ->orderBy('order_num'),
            ])
            ->select('id', 'title', 'slug', 'doc_menu_id', 'order_num', 'updated_at')
            ->orderBy('order_num')
            ->get()
            ->groupBy('doc_menu_id');

        $allMenus = DocMenu::orderBy('order_num')->orderBy('name')->get();

        return $allMenus->whereNull('parent_id')->values()->map(function ($main) use ($allMenus, $publishedPages) {
            $main->setRelation(
                'pages',
                collect($publishedPages->get($main->id, collect()))->values()
            );

            $children = $allMenus
                ->where('parent_id', $main->id)
                ->values()
                ->map(function ($sub) use ($publishedPages) {
                    $sub->setRelation(
                        'pages',
                        collect($publishedPages->get($sub->id, collect()))->values()
                    );

                    return $sub;
                })
                ->filter(fn($sub) => $sub->pages->isNotEmpty())
                ->values();

            $main->setRelation('children', $children);

            return $main;
        })->filter(fn($main) => $main->pages->isNotEmpty() || $main->children->isNotEmpty())
            ->values();
    }

    /**
     * Legacy flat endpoint — grouped by doc_menu name for backward compat.
     */
    public function index(): JsonResponse
    {
        $docs = Doc::where('status', 'published')
            ->whereNull('parent_id')
            ->with([
                'docMenu',
                'children' => fn($q) => $q
                    ->where('status', 'published')
                    ->select('id', 'title', 'slug', 'parent_id', 'order_num', 'updated_at')
                    ->orderBy('order_num')
                    ->with([
                        'children' => fn($q2) => $q2
                            ->where('status', 'published')
                            ->select('id', 'title', 'slug', 'parent_id', 'order_num', 'updated_at')
                            ->orderBy('order_num')
                    ])
            ])
            ->select('id', 'title', 'slug', 'category', 'category_order', 'order_num', 'parent_id', 'doc_menu_id', 'updated_at')
            ->orderBy('category_order')
            ->orderBy('order_num')
            ->get()
            ->groupBy(fn($d) => $d->docMenu?->name ?? $d->category ?? 'General');

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

