<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Cloudinary\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class StorageStatsController extends Controller
{
    public function index(): JsonResponse
    {
        $data = $this->getCachedStats();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function refresh(): JsonResponse
    {
        try {
            Cache::forget('storage_stats');
        } catch (\Throwable) {
            // Cache unavailable — ignore
        }

        return $this->index();
    }

    private function getCachedStats(): array
    {
        $compute = fn() => [
            'cloudinary' => $this->getCloudinaryStats(),
            'supabase' => $this->getSupabaseStats(),
            'redis' => $this->getRedisStats(),
            'database' => $this->getDatabaseStats(),
        ];

        try {
            return Cache::remember('storage_stats', 3600, $compute);
        } catch (\Throwable $e) {
            Log::warning('Cache unavailable for storage_stats, fetching directly: ' . $e->getMessage());
            return $compute();
        }
    }

    private function getCloudinaryStats(): array
    {
        try {
            $cloudUrl = config('cloudinary.cloud_url');
            if (empty($cloudUrl)) {
                return $this->errorResult('Cloudinary', 'CLOUDINARY_URL not configured');
            }

            $cloudinary = new Cloudinary($cloudUrl);
            $usage = $cloudinary->adminApi()->usage();

            // Cloudinary API may return 'limit' or 'allowed' or neither
            // Free plan defaults: 25 GB storage, 25 GB bandwidth, 25,000 transformations
            $freeStorageLimit = 25 * 1024 * 1024 * 1024;   // 25 GB
            $freeBandwidthLimit = 25 * 1024 * 1024 * 1024; // 25 GB
            $freeTransformLimit = 25000;

            $storageUsed = $usage['storage']['usage'] ?? 0;
            $storageLimit = $usage['storage']['limit'] ?? $usage['storage']['allowed'] ?? $freeStorageLimit;
            $bandwidthUsed = $usage['bandwidth']['usage'] ?? 0;
            $bandwidthLimit = $usage['bandwidth']['limit'] ?? $usage['bandwidth']['allowed'] ?? $freeBandwidthLimit;
            $transformations = $usage['transformations']['usage'] ?? 0;
            $transformationsLimit = $usage['transformations']['limit'] ?? $usage['transformations']['allowed'] ?? $freeTransformLimit;
            $resources = $usage['resources'] ?? 0;

            return [
                'configured' => true,
                'plan' => $usage['plan'] ?? 'Free',
                'storage' => [
                    'used' => $storageUsed,
                    'limit' => $storageLimit,
                    'used_formatted' => $this->formatBytes($storageUsed),
                    'limit_formatted' => $this->formatBytes($storageLimit),
                    'percentage' => $storageLimit > 0 ? round(($storageUsed / $storageLimit) * 100, 1) : 0,
                ],
                'bandwidth' => [
                    'used' => $bandwidthUsed,
                    'limit' => $bandwidthLimit,
                    'used_formatted' => $this->formatBytes($bandwidthUsed),
                    'limit_formatted' => $this->formatBytes($bandwidthLimit),
                    'percentage' => $bandwidthLimit > 0 ? round(($bandwidthUsed / $bandwidthLimit) * 100, 1) : 0,
                ],
                'transformations' => [
                    'used' => $transformations,
                    'limit' => $transformationsLimit,
                    'percentage' => $transformationsLimit > 0 ? round(($transformations / $transformationsLimit) * 100, 1) : 0,
                ],
                'resources' => $resources,
            ];
        } catch (\Throwable $e) {
            Log::error('Cloudinary stats error: ' . $e->getMessage());
            return $this->errorResult('Cloudinary', $e->getMessage());
        }
    }

    private function getSupabaseStats(): array
    {
        try {
            $url = rtrim(config('services.supabase.url'), '/');
            $key = config('services.supabase.secret_key');
            $bucket = config('services.supabase.bucket');

            if (empty($url) || empty($key)) {
                return $this->errorResult('Supabase', 'Supabase not configured');
            }

            // List all objects in the bucket to calculate total size
            $totalSize = 0;
            $fileCount = 0;
            $folderStats = [];

            $folders = ['covers', 'projects', 'team', 'media', 'avatars'];

            foreach ($folders as $folder) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                ])->post("{$url}/storage/v1/object/list/{$bucket}", [
                            'prefix' => $folder,
                            'limit' => 1000,
                            'offset' => 0,
                        ]);

                if ($response->successful()) {
                    $files = collect($response->json())->filter(fn($item) => isset($item['metadata']['size']));
                    $folderSize = $files->sum(fn($item) => $item['metadata']['size'] ?? 0);
                    $folderCount = $files->count();

                    $folderStats[$folder] = [
                        'files' => $folderCount,
                        'size' => $folderSize,
                        'size_formatted' => $this->formatBytes($folderSize),
                    ];

                    $totalSize += $folderSize;
                    $fileCount += $folderCount;
                }
            }

            // Supabase free tier: 1GB storage
            $limit = 1 * 1024 * 1024 * 1024; // 1 GB

            return [
                'configured' => true,
                'storage' => [
                    'used' => $totalSize,
                    'limit' => $limit,
                    'used_formatted' => $this->formatBytes($totalSize),
                    'limit_formatted' => $this->formatBytes($limit),
                    'percentage' => $limit > 0 ? round(($totalSize / $limit) * 100, 1) : 0,
                ],
                'files' => $fileCount,
                'folders' => $folderStats,
                'bucket' => $bucket,
            ];
        } catch (\Throwable $e) {
            Log::error('Supabase stats error: ' . $e->getMessage());
            return $this->errorResult('Supabase', $e->getMessage());
        }
    }

    private function getRedisStats(): array
    {
        try {
            $host = env('REDIS_HOST', '');
            if (empty($host)) {
                return ['configured' => false, 'error' => 'Redis not configured'];
            }

            $rawInfo = Redis::connection()->info();

            // Predis 2.x returns INFO as nested sections; flatten to a single array,
            // skipping any values that are still arrays (e.g. keyspace sub-arrays).
            $info = [];
            foreach ($rawInfo as $sectionKey => $sectionVal) {
                if (is_array($sectionVal)) {
                    foreach ($sectionVal as $k => $v) {
                        if (!is_array($v)) {
                            $info[$k] = $v;
                        }
                    }
                } elseif (!is_array($sectionVal)) {
                    $info[$sectionKey] = $sectionVal;
                }
            }

            $usedMemory = (int) ($info['used_memory'] ?? 0);
            // Upstash free tier limit: 256 MB
            $maxMemory = (int) ($info['maxmemory'] ?? 0);
            $memLimit = $maxMemory > 0 ? $maxMemory : (256 * 1024 * 1024);

            // Key count across all DBs (keyspace section e.g. db0 => "keys=3,expires=0")
            $totalKeys = 0;
            foreach ($info as $k => $v) {
                if (is_string($v) && is_string($k) && str_starts_with($k, 'db')) {
                    preg_match('/keys=(\d+)/', $v, $m);
                    $totalKeys += (int) ($m[1] ?? 0);
                }
            }

            $isUpstash = str_contains($host, 'upstash.io');

            return [
                'configured' => true,
                'provider' => $isUpstash ? 'Upstash' : 'Redis',
                'version' => $info['redis_version'] ?? '?',
                'memory' => [
                    'used' => $usedMemory,
                    'limit' => $memLimit,
                    'used_formatted' => $this->formatBytes($usedMemory),
                    'limit_formatted' => $this->formatBytes($memLimit),
                    'percentage' => $memLimit > 0 ? round(($usedMemory / $memLimit) * 100, 1) : 0,
                ],
                'keys' => $totalKeys,
                'commands_processed' => (int) ($info['total_commands_processed'] ?? 0),
                'connected_clients' => (int) ($info['connected_clients'] ?? 0),
                'uptime_seconds' => (int) ($info['uptime_in_seconds'] ?? 0),
            ];
        } catch (\Throwable $e) {
            Log::error('Redis stats error: ' . $e->getMessage());
            return ['configured' => false, 'error' => $e->getMessage()];
        }
    }

    private function getDatabaseStats(): array
    {
        try {
            $host = config('database.connections.pgsql.host', '');
            $dbName = config('database.connections.pgsql.database', 'postgres');

            $sizeRow = DB::selectOne('SELECT pg_database_size(current_database()) AS size_bytes');
            $sizeBytes = (int) ($sizeRow->size_bytes ?? 0);

            $tableRow = DB::selectOne(
                "SELECT count(*) AS cnt FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'"
            );
            $tableCount = (int) ($tableRow->cnt ?? 0);

            $rowCounts = DB::select(
                "SELECT relname AS table_name, n_live_tup AS row_count
                 FROM pg_stat_user_tables
                 ORDER BY n_live_tup DESC
                 LIMIT 10"
            );

            // Neon free: 512 MB; Render/generic: 1 GB
            $isNeon = str_contains($host, 'neon.tech');
            $limit = $isNeon ? (512 * 1024 * 1024) : (1024 * 1024 * 1024);
            $provider = $isNeon ? 'Neon' : 'PostgreSQL';

            return [
                'configured' => true,
                'provider' => $provider,
                'database' => $dbName,
                'storage' => [
                    'used' => $sizeBytes,
                    'limit' => $limit,
                    'used_formatted' => $this->formatBytes($sizeBytes),
                    'limit_formatted' => $this->formatBytes($limit),
                    'percentage' => $limit > 0 ? round(($sizeBytes / $limit) * 100, 1) : 0,
                ],
                'tables' => $tableCount,
                'top_tables' => array_map(fn($r) => [
                    'name' => $r->table_name,
                    'rows' => (int) $r->row_count,
                ], $rowCounts),
            ];
        } catch (\Throwable $e) {
            Log::error('Database stats error: ' . $e->getMessage());
            return ['configured' => false, 'error' => $e->getMessage()];
        }
    }

    private function errorResult(string $provider, string $message): array
    {
        return [
            'configured' => false,
            'error' => $message,
            'storage' => [
                'used' => 0,
                'limit' => 0,
                'used_formatted' => '0 B',
                'limit_formatted' => '0 B',
                'percentage' => 0,
            ],
        ];
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
