<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class SupabaseStorage
{
    protected string $url;
    protected string $key;
    protected string $bucket;
    /** @var list<string> */
    protected array $deadHosts;

    public function __construct()
    {
        // Env may be unset in local/docker; never pass null into rtrim() or typed string props (PHP 8+ TypeError).
        $rawUrl = config('services.supabase.url');
        $this->url = rtrim(is_string($rawUrl) ? $rawUrl : '', '/');
        $this->key = (string) (config('services.supabase.secret_key') ?? '');
        $bucket = config('services.supabase.bucket');
        $this->bucket = (is_string($bucket) && $bucket !== '') ? $bucket : 'kalapak-assets';
        $deadHosts = config('services.supabase.dead_hosts', []);
        $this->deadHosts = is_array($deadHosts) ? array_map('strtolower', $deadHosts) : [];
    }

    /**
     * Upload a file to Supabase Storage.
     */
    public function upload(UploadedFile $file, string $folder = ''): string|false
    {
        $filename = $folder . '/' . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filename = ltrim($filename, '/');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
            'Content-Type' => $file->getMimeType(),
            'x-upsert' => 'true',
        ])->withBody(
                file_get_contents($file->getRealPath()),
                $file->getMimeType()
            )->post("{$this->url}/storage/v1/object/{$this->bucket}/{$filename}");

        if ($response->successful()) {
            return $filename;
        }

        throw new \RuntimeException(
            'Supabase upload failed: ' . $response->body()
        );
    }

    /**
     * Delete a file from Supabase Storage.
     */
    public function delete(string $path): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
        ])->delete("{$this->url}/storage/v1/object/{$this->bucket}", [
                    'prefixes' => [$path],
                ]);

        return $response->successful();
    }

    /**
     * Returns true when Supabase is configured with real credentials.
     */
    public function isConfigured(): bool
    {
        if (empty($this->url) || empty($this->key)) {
            return false;
        }

        $urlLower = strtolower($this->url);
        $keyLower = strtolower($this->key);
        $host = strtolower((string) parse_url($this->url, PHP_URL_HOST));

        if (
            str_contains($urlLower, 'your_project_id') ||
            str_contains($host, 'your_project_id') ||
            $keyLower === 'your-anon-key' ||
            $keyLower === 'your-service-role-key'
        ) {
            return false;
        }

        if ($host !== '' && $this->isDeadHost($host)) {
            return false;
        }

        return true;
    }

    public function placeholderUrl(): string
    {
        $url = config('services.media_placeholder_url');

        return is_string($url) && $url !== '' ? $url : '';
    }

    public function resolvePublicUrlOrPlaceholder(?string $pathOrUrl): ?string
    {
        return $this->resolvePublicUrl($pathOrUrl) ?? ($pathOrUrl ? $this->placeholderUrl() ?: null : null);
    }

    public function isDeadHostUrl(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }

        $candidate = trim($url);
        if (!str_contains($candidate, '://') && str_contains($candidate, '.supabase.co/')) {
            $candidate = 'https://' . ltrim($candidate, '/');
        }

        $host = strtolower((string) parse_url($candidate, PHP_URL_HOST));

        return $host !== '' && $this->isDeadHost($host);
    }

    protected function isDeadHost(string $host): bool
    {
        return in_array(strtolower($host), $this->deadHosts, true);
    }

    protected function sanitizeResolvedUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        return $this->isDeadHostUrl($url) ? null : $url;
    }

    /**
     * Get the public URL for a file, or null when Supabase is not configured.
     */
    public function url(string $path): ?string
    {
        return $this->resolvePublicUrl($path);
    }

    /**
     * Resolve a storage path or any Supabase public URL to the currently configured project.
     */
    public function resolvePublicUrl(?string $pathOrUrl): ?string
    {
        if ($pathOrUrl === null) {
            return null;
        }

        $pathOrUrl = trim($pathOrUrl);
        if ($pathOrUrl === '') {
            return null;
        }

        if (str_starts_with($pathOrUrl, '//')) {
            $pathOrUrl = 'https:' . $pathOrUrl;
        }

        $storagePath = $this->extractStoragePath($pathOrUrl);
        if ($storagePath !== null) {
            if (!$this->isConfigured()) {
                return null;
            }

            return $this->sanitizeResolvedUrl(
                "{$this->url}/storage/v1/object/public/{$this->bucket}/{$storagePath}"
            );
        }

        if (preg_match('/^https?:\/\//i', $pathOrUrl) || str_contains($pathOrUrl, '.supabase.co/')) {
            $absolute = preg_match('/^https?:\/\//i', $pathOrUrl)
                ? $pathOrUrl
                : 'https://' . ltrim($pathOrUrl, '/');

            return $this->sanitizeResolvedUrl($absolute);
        }

        if (!$this->isConfigured()) {
            return null;
        }

        return $this->sanitizeResolvedUrl(
            "{$this->url}/storage/v1/object/public/{$this->bucket}/" . ltrim($pathOrUrl, '/')
        );
    }

    /**
     * Rewrite Supabase storage URLs inside HTML (e.g. blog post bodies).
     */
    public function rewriteUrlsInHtml(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        $html = (string) preg_replace_callback(
            '#\ssrc=(["\'])((?:https?:)?//[a-z0-9-]+\.supabase\.co/storage/v1/object/public/[^"\']+)\1#i',
            function (array $matches): string {
                $quote = $matches[1];
                $resolved = $this->resolvePublicUrl($matches[2]);

                return $resolved
                    ? ' src=' . $quote . $resolved . $quote
                    : ' src=' . $quote . $quote;
            },
            $html
        );

        if (!$this->isConfigured()) {
            return (string) preg_replace(
                '#\ssrc=(["\'])(?!https?://|/|data:)([^"\']+\.(?:png|jpe?g|gif|webp|svg))\1#i',
                ' src=$1$1',
                $html
            );
        }

        return (string) preg_replace_callback(
            '#\ssrc=(["\'])(?!https?://|/|data:)([^"\']+\.(?:png|jpe?g|gif|webp|svg))\1#i',
            function (array $matches): string {
                $resolved = $this->resolvePublicUrl($matches[2]);
                $quote = $matches[1];

                return $resolved
                    ? ' src=' . $quote . $resolved . $quote
                    : ' src=' . $quote . $quote;
            },
            $html
        );
    }

    /**
     * Extract object path (e.g. blog/foo.png) from a Supabase public storage URL.
     */
    public function extractStoragePath(string $pathOrUrl): ?string
    {
        $candidate = trim($pathOrUrl);
        if ($candidate === '') {
            return null;
        }

        if (!str_contains($candidate, '://') && str_contains($candidate, '.supabase.co/')) {
            $candidate = 'https://' . ltrim($candidate, '/');
        }

        $pattern = '#^https?://[^/]+\.supabase\.co/storage/v1/object/public/' . preg_quote($this->bucket, '#') . '/(.+)$#i';

        if (preg_match($pattern, $candidate, $matches)) {
            return ltrim($matches[1], '/');
        }

        // Any Supabase project host — remap to the configured bucket path.
        $genericPattern = '#^https?://[^/]+\.supabase\.co/storage/v1/object/public/[^/]+/(.+)$#i';
        if (preg_match($genericPattern, $candidate, $matches)) {
            return ltrim($matches[1], '/');
        }

        return null;
    }

    /**
     * Test the connection by uploading and deleting a test file.
     */
    public function test(): array
    {
        $result = [
            'url' => $this->url,
            'bucket' => $this->bucket,
            'key_set' => !empty($this->key),
        ];

        try {
            $testPath = '_test_' . time() . '.txt';
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'text/plain',
                'x-upsert' => 'true',
            ])->withBody('test', 'text/plain')
                ->post("{$this->url}/storage/v1/object/{$this->bucket}/{$testPath}");

            if ($response->successful()) {
                $this->delete($testPath);
                $result['upload_test'] = 'SUCCESS';
            } else {
                $result['upload_test'] = 'FAILED';
                $result['error'] = $response->body();
                $result['status_code'] = $response->status();
            }
        } catch (\Exception $e) {
            $result['upload_test'] = 'FAILED';
            $result['error'] = $e->getMessage();
        }

        return $result;
    }
}
