<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenant = app(\App\Support\TenantContext::class)->get();
        $user = $request->user();
        $currentOrganization = null;

        if ($tenant && $user) {
            $currentOrganization = [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'role' => $tenant->roleFor($user),
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'currentOrganization' => $currentOrganization,
        ];
    }
}
