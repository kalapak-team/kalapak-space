<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class DdosProtection
{
    /**
     * Max requests allowed per second per IP before triggering block.
     * Legitimate users rarely exceed 5 req/s even with aggressive browsing.
     */
    private const REQUESTS_PER_SECOND = 10;

    /**
     * Max requests allowed per 10-second window per IP.
     * Catches sustained bursts that stay just under the per-second limit.
     */
    private const REQUESTS_PER_10_SECONDS = 50;

    /** Duration (seconds) an IP stays blocked after exceeding limits. */
    private const BLOCK_DURATION = 300; // 5 minutes

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if (Cache::has("ddos_blocked:{$ip}")) {
            return response()->json([
                'message' => 'Too many requests. Your IP has been temporarily blocked.',
            ], 429)->withHeaders([
                'Retry-After' => self::BLOCK_DURATION,
            ]);
        }

        $keyPerSec = "ddos_ps:{$ip}:" . now()->timestamp;
        $keyPer10s = "ddos_p10s:{$ip}:" . intdiv(now()->timestamp, 10);

        $hitsPerSec = Cache::increment($keyPerSec);
        if ($hitsPerSec === 1) {
            Cache::put($keyPerSec, 1, 2);
        }

        $hitsPer10s = Cache::increment($keyPer10s);
        if ($hitsPer10s === 1) {
            Cache::put($keyPer10s, 1, 15);
        }

        if ($hitsPerSec > self::REQUESTS_PER_SECOND || $hitsPer10s > self::REQUESTS_PER_10_SECONDS) {
            Cache::put("ddos_blocked:{$ip}", true, self::BLOCK_DURATION);

            return response()->json([
                'message' => 'Too many requests. Your IP has been temporarily blocked.',
            ], 429)->withHeaders([
                'Retry-After' => self::BLOCK_DURATION,
            ]);
        }

        return $next($request);
    }
}
