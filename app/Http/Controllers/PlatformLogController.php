<?php

namespace App\Http\Controllers;

use App\Models\PlatformActivityLog;
use App\Models\PlatformCrashLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PlatformLogController extends Controller
{
    public function activity(Request $request): InertiaResponse
    {
        $this->requireSuperAdmin($request);

        $search = trim((string) $request->query('search', ''));
        $statusFilter = trim((string) $request->query('status', 'all'));

        $query = PlatformActivityLog::with('user:id,name,email')->latest();

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('url', 'like', "%{$search}%")
                    ->orWhere('method', 'like', "%{$search}%")
                    ->orWhere('ip', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($statusFilter === '2xx') {
            $query->whereBetween('status_code', [200, 299]);
        } elseif ($statusFilter === '3xx') {
            $query->whereBetween('status_code', [300, 399]);
        } elseif ($statusFilter === '4xx') {
            $query->whereBetween('status_code', [400, 499]);
        } elseif ($statusFilter === '5xx') {
            $query->whereBetween('status_code', [500, 599]);
        }

        $logs = $query->paginate(20)->withQueryString();

        $stats = [
            'total'     => PlatformActivityLog::count(),
            'today'     => PlatformActivityLog::whereDate('created_at', today())->count(),
            'errors'    => PlatformActivityLog::where('status_code', '>=', 400)->count(),
            'avg_ms'    => (int) PlatformActivityLog::whereDate('created_at', today())->avg('duration_ms'),
        ];

        return Inertia::render('superadmin/logs', [
            'tab'     => 'activity',
            'logs'    => $logs,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
            ],
            'stats'   => $stats,
            'logging_enabled' => $this->isLoggingEnabled(),
        ]);
    }

    public function crashes(Request $request): InertiaResponse
    {
        $this->requireSuperAdmin($request);

        $search = trim((string) $request->query('search', ''));

        $query = PlatformCrashLog::with('user:id,name,email')->latest();

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('ip', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        // Trim trace to 10 lines for each log in the response
        $logs->getCollection()->transform(function (PlatformCrashLog $log) {
            $log->setAttribute('trimmed_trace', $log->trimmedTrace(10));
            return $log;
        });

        $stats = [
            'total'  => PlatformCrashLog::count(),
            'today'  => PlatformCrashLog::whereDate('created_at', today())->count(),
            'server' => PlatformCrashLog::where('status_code', '>=', 500)->count(),
        ];

        return Inertia::render('superadmin/logs', [
            'tab'     => 'crashes',
            'logs'    => $logs,
            'filters' => ['search' => $search],
            'stats'   => $stats,
            'logging_enabled' => $this->isLoggingEnabled(),
        ]);
    }

    public function showCrash(Request $request, PlatformCrashLog $log): InertiaResponse
    {
        $this->requireSuperAdmin($request);

        $log->load('user:id,name,email');
        $log->setAttribute('trimmed_trace', $log->trimmedTrace(10));

        return Inertia::render('superadmin/crash-detail', [
            'log' => $log,
        ]);
    }

    public function toggleLogging(Request $request): RedirectResponse
    {
        $this->requireSuperAdmin($request);

        $current = $this->isLoggingEnabled();
        Cache::forever('platform_logging_enabled', ! $current);

        $label = ! $current ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('status', "Platform logging berhasil {$label}.");
    }

    private function isLoggingEnabled(): bool
    {
        return Cache::get('platform_logging_enabled', (bool) config('kuesify.platform_logging', true));
    }

    private function requireSuperAdmin(Request $request): void
    {
        $user = $request->user();
        abort_unless($user, 403);

        $sessionRole = (string) $request->session()->get('active_role', '');
        if ($sessionRole === 'super_admin') {
            return;
        }

        $orgId = app(\App\Support\TenantContext::class)->id();
        $org = $orgId ? \App\Models\Organization::find($orgId) : null;
        $isSuper = ($org && $org->roleFor($user) === 'super_admin') ||
            $user->organizations()->wherePivot('role', 'super_admin')->exists();

        abort_unless($isSuper, 403);
    }
}
