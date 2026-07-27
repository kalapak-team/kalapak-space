<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Models\Doc;
use App\Models\DocMenu;
use App\Models\Setting;
use App\Support\PublicApiCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'name' => 'Kalapak Code Team API',
            'version' => '1.0.0',
        ]);
    }

    public function settings(): JsonResponse
    {
        $settings = Cache::remember(PublicApiCache::KEY_SETTINGS, PublicApiCache::ttl(), function () {
            return Setting::whereIn('group', ['general', 'social', 'seo'])->get()
                ->mapWithKeys(fn ($s) => [$s->key => $s->value]);
        });

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}
