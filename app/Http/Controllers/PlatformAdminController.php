<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Organization;
use App\Models\QuizAttempt;
use App\Models\AiGeneration;
use App\Models\User;
use App\Services\ReverbHealthService;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PlatformAdminController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $this->requireSuperAdmin($request);

        $reverbHealth = app(ReverbHealthService::class)->check();

        $totalAttemptsMonth = QuizAttempt::withoutGlobalScopes()->where('created_at', '>=', now()->startOfMonth())->count();
        $aiGenerationsMonth = AiGeneration::withoutGlobalScopes()->where('created_at', '>=', now()->startOfMonth())->count();
        $aiFailures = AiGeneration::withoutGlobalScopes()->where('status', 'failed')->count();

        $maintenance = Cache::get('platform_maintenance', [
            'status' => 'Sistem Normal (Aktif)',
            'jadwal_rutin' => 'Setiap Minggu, 02:00 - 04:00 WIB',
            'mode' => 'Live Production',
        ]);

        $aiConfig = Cache::get('platform_ai_config', [
            'weekly_creator_limit' => (int) config('services.gemini.weekly_creator_quota', 10),
            'generasi_bulan_ini' => $aiGenerationsMonth,
            'ai_model' => 'Gemini 2.5 Flash',
        ]);

        $audioSettings = Cache::get('platform_audio_settings', $this->defaultAudioSettings());

        $monthlyStats = [
            'total_organizations' => Organization::count(),
            'total_users' => User::count(),
            'total_attempts_month' => $totalAttemptsMonth,
            'ai_generations_month' => $aiGenerationsMonth,
        ];

        return Inertia::render('Workspace', [
            'title' => 'Platform Admin',
            'section' => 'admin',
            'items' => Organization::withCount('members')->latest()->paginate(20),
            'filters' => [],
            'summary' => [
                'is_super_admin' => true,
                'categories' => Category::orderBy('name')->get(['id', 'name']),
                'health' => array_merge([
                    'queued_jobs' => \DB::table('jobs')->count(),
                    'failed_jobs' => \DB::table('failed_jobs')->count(),
                    'ai_failures' => $aiFailures,
                ], $reverbHealth),
                'maintenance' => $maintenance,
                'ai_config' => $aiConfig,
                'audio_settings' => $audioSettings,
                'monthly_stats' => $monthlyStats,
            ],
        ]);
    }

    public function users(Request $request): InertiaResponse
    {
        $this->requireSuperAdmin($request);

        $search = trim((string) $request->query('search', ''));
        $roleFilter = trim((string) $request->query('role', 'all'));
        $statusFilter = trim((string) $request->query('status', 'all'));

        $query = User::with(['organizations' => function ($q) {
            $q->select('organizations.id', 'organizations.name');
        }])
            ->withCount(['createdQuizzes', 'attempts'])
            ->latest();

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== '' && $roleFilter !== 'all') {
            $query->whereHas('organizations', function (Builder $q) use ($roleFilter) {
                $q->wherePivot('role', $roleFilter);
            });
        }

        if ($statusFilter === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($statusFilter === 'unverified') {
            $query->whereNull('email_verified_at');
        }

        $users = $query->paginate(15)->withQueryString();

        $totalUsers = User::count();
        $verifiedCount = User::whereNotNull('email_verified_at')->count();
        $unverifiedCount = User::whereNull('email_verified_at')->count();

        return Inertia::render('Admin/Users', [
            'title' => 'Monitoring Pengguna Platform',
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
                'status' => $statusFilter,
            ],
            'stats' => [
                'total_users' => $totalUsers,
                'verified_users' => $verifiedCount,
                'unverified_users' => $unverifiedCount,
                'total_organizations' => Organization::count(),
            ],
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $this->requireSuperAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:categories,name'],
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('status', 'Kategori baru berhasil ditambahkan.');
    }

    public function destroyCategory(Request $request, Category $category): RedirectResponse
    {
        $this->requireSuperAdmin($request);

        $category->delete();

        return back()->with('status', 'Kategori berhasil dihapus.');
    }

    public function updateMaintenance(Request $request): RedirectResponse
    {
        $this->requireSuperAdmin($request);

        $validated = $request->validate([
            'status' => ['required', 'string', 'max:100'],
            'jadwal_rutin' => ['required', 'string', 'max:100'],
            'mode' => ['required', 'string', 'max:50'],
        ]);

        Cache::forever('platform_maintenance', $validated);

        return back()->with('status', 'Pengaturan pemeliharaan berhasil diperbarui.');
    }

    public function updateAiConfig(Request $request): RedirectResponse
    {
        $this->requireSuperAdmin($request);

        $validated = $request->validate([
            'weekly_creator_limit' => ['required', 'integer', 'min:1', 'max:500'],
            'ai_model' => ['required', 'string', 'max:60'],
        ]);

        $current = Cache::get('platform_ai_config', []);
        $current['weekly_creator_limit'] = $validated['weekly_creator_limit'];
        $current['ai_model'] = $validated['ai_model'];

        Cache::forever('platform_ai_config', $current);

        return back()->with('status', 'Limit & model AI berhasil disimpan.');
    }

    public function updateAudioSettings(Request $request): RedirectResponse
    {
        $this->requireSuperAdmin($request);

        $validated = $request->validate([
            'master_volume' => ['required', 'integer', 'min:0', 'max:100'],
            'contexts' => ['required', 'array'],
            'contexts.*.label' => ['required', 'string'],
            'contexts.*.preset' => ['required', 'string'],
            'contexts.*.custom_url' => ['nullable', 'string', 'max:500'],
            'contexts.*.volume' => ['required', 'integer', 'min:0', 'max:100'],
            'contexts.*.enabled' => ['required', 'boolean'],
        ]);

        Cache::forever('platform_audio_settings', $validated);

        return back()->with('status', 'Konfigurasi Audio & SFX multi-konteks berhasil disimpan.');
    }

    private function defaultAudioSettings(): array
    {
        return [
            'master_volume' => 75,
            'contexts' => [
                'bgm_lobby' => [
                    'label' => 'BGM Lobby & Waiting Room',
                    'preset' => 'Arcade Retro 8-bit (Default)',
                    'custom_url' => '',
                    'volume' => 70,
                    'enabled' => true,
                ],
                'bgm_gameplay' => [
                    'label' => 'BGM Gameplay / Soal Berjalan',
                    'preset' => 'Ticking Pulse Electro',
                    'custom_url' => '',
                    'volume' => 65,
                    'enabled' => true,
                ],
                'bgm_podium' => [
                    'label' => 'BGM Podium Juara & Kemenangan',
                    'preset' => 'Grand Champion Fanfare',
                    'custom_url' => '',
                    'volume' => 80,
                    'enabled' => true,
                ],
                'sfx_correct' => [
                    'label' => 'SFX Jawaban Benar',
                    'preset' => 'Crystal Chime High',
                    'custom_url' => '',
                    'volume' => 90,
                    'enabled' => true,
                ],
                'sfx_wrong' => [
                    'label' => 'SFX Jawaban Salah',
                    'preset' => 'Muted Buzzer Low',
                    'custom_url' => '',
                    'volume' => 75,
                    'enabled' => true,
                ],
                'sfx_tick' => [
                    'label' => 'SFX Detik Kritis (Countdown 5s)',
                    'preset' => 'Clock Tick Fast',
                    'custom_url' => '',
                    'volume' => 80,
                    'enabled' => true,
                ],
                'sfx_streak' => [
                    'label' => 'SFX Streak Combo Juara',
                    'preset' => 'Powerup Spark Chord',
                    'custom_url' => '',
                    'volume' => 85,
                    'enabled' => true,
                ],
            ],
        ];
    }

    private function requireSuperAdmin(Request $request): void
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $sessionRole = (string) $request->session()->get('active_role', '');
        if ($sessionRole === 'super_admin') {
            return;
        }

        $orgId = app(TenantContext::class)->id();
        $org = $orgId ? Organization::find($orgId) : null;
        $isSuper = ($org && $org->roleFor($user) === 'super_admin') ||
            $user->organizations()->wherePivot('role', 'super_admin')->exists();

        abort_unless($isSuper, 403);
    }
}
