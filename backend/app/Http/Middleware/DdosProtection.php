<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class DdosProtection
{
    private const REQUESTS_PER_SECOND = 10;
    private const REQUESTS_PER_10_SECONDS = 50;
    private const BLOCK_DURATION = 300; // 5 minutes

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $ip = $request->ip();

            if (Cache::get("ddos_blocked:{$ip}")) {
                return response()->json([
                    'message' => 'Too many requests. Your IP has been temporarily blocked.',
                ], 429)->withHeaders([
                    'Retry-After' => self::BLOCK_DURATION,
                ]);
            }

            $keyPerSec = "ddos_ps:{$ip}:" . now()->timestamp;
            $keyPer10s = "ddos_p10s:{$ip}:" . intdiv(now()->timestamp, 10);

            $hitsPerSec = (int) Cache::get($keyPerSec, 0) + 1;
            Cache::put($keyPerSec, $hitsPerSec, 2);

            $hitsPer10s = (int) Cache::get($keyPer10s, 0) + 1;
            Cache::put($keyPer10s, $hitsPer10s, 15);

            if ($hitsPerSec > self::REQUESTS_PER_SECOND || $hitsPer10s > self::REQUESTS_PER_10_SECONDS) {
                Cache::put("ddos_blocked:{$ip}", true, self::BLOCK_DURATION);

                return response()->json([
                    'message' => 'Too many requests. Your IP has been temporarily blocked.',
                ], 429)->withHeaders([
                    'Retry-After' => self::BLOCK_DURATION,
                ]);
            }
        } catch (\Throwable) {
            // Never let DDoS protection crash the app — fail open
        }

        return $next($request);
    }
}
