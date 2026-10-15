<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\PlatformActivityLog;
use App\Models\PlatformCrashLog;
use Symfony\Component\HttpFoundation\Response;

class PlatformLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        // Toggle: skip logging entirely when disabled
        if (! $this->isEnabled()) {
            return $next($request);
        }

        $requestId = (string) Str::uuid();
        $request->headers->set('X-Request-Id', $requestId);

        $start = microtime(true);

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            // Log crash then re-throw so Laravel's exception handler still works
            $this->logCrash($request, $requestId, $e);
            throw $e;
        }

        $duration = (int) round((microtime(true) - $start) * 1000);

        try {
            PlatformActivityLog::create([
                'user_id'      => $request->user()?->id,
                'request_id'   => $requestId,
                'method'       => $request->method(),
                'url'          => Str::limit($request->fullUrl(), 2000),
                'status_code'  => $response->getStatusCode(),
                'duration_ms'  => $duration,
                'ip'           => $request->ip(),
                'user_agent'   => Str::limit($request->userAgent(), 500),
                'request_body' => $this->sanitizeBody($request->all()),
            ]);

            // If response is a server error (5xx), also log as crash
            if ($response->getStatusCode() >= 500) {
                PlatformCrashLog::create([
                    'user_id'      => $request->user()?->id,
                    'request_id'   => $requestId,
                    'method'       => $request->method(),
                    'url'          => Str::limit($request->fullUrl(), 2000),
                    'status_code'  => $response->getStatusCode(),
                    'message'      => 'HTTP ' . $response->getStatusCode() . ' response',
                    'trace'        => null,
                    'request_body' => $this->sanitizeBody($request->all()),
                    'ip'           => $request->ip(),
                    'user_agent'   => Str::limit($request->userAgent(), 500),
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }

    private function logCrash(Request $request, string $requestId, \Throwable $e): void
    {
        try {
            // Trim stack trace to max 10 lines to minimize RAM usage
            $traceLines = explode("\n", $e->getTraceAsString());
            $maxLines = 10;
            $trimmedTrace = implode("\n", array_slice($traceLines, 0, $maxLines));
            if (count($traceLines) > $maxLines) {
                $trimmedTrace .= "\n... (" . (count($traceLines) - $maxLines) . ' more lines truncated)';
            }

            PlatformCrashLog::create([
                'user_id'      => $request->user()?->id,
                'request_id'   => $requestId,
                'method'       => $request->method(),
                'url'          => Str::limit($request->fullUrl(), 2000),
                'status_code'  => method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500,
                'message'      => Str::limit($e->getMessage(), 1000),
                'trace'        => $trimmedTrace,
                'request_body' => $this->sanitizeBody($request->all()),
                'ip'           => $request->ip(),
                'user_agent'   => Str::limit($request->userAgent(), 500),
            ]);
        } catch (\Throwable $logErr) {
            report($logErr);
        }
    }

    private function sanitizeBody(array $data): ?array
    {
        if (empty($data)) {
            return null;
        }

        unset(
            $data['password'],
            $data['password_confirmation'],
            $data['current_password'],
            $data['token'],
            $data['_token'],
            $data['signature'],
        );

        return $data;
    }

    private function isEnabled(): bool
    {
        return Cache::get('platform_logging_enabled', (bool) config('kuesify.platform_logging', true));
    }
}
