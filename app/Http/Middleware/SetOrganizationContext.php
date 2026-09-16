<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetOrganizationContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->session()->get('organization_id');
        $organization = Organization::find($organizationId);

        if (! $organization || ! $request->user()?->organizations()->whereKey($organization)->wherePivot('is_active', true)->exists()) {
            $organization = $request->user()?->organizations()->wherePivot('is_active', true)->orderBy('organizations.id')->first();
            $request->session()->put('organization_id', $organization?->id);
        }

        abort_unless($organization && $request->user()?->organizations()->whereKey($organization)->wherePivot('is_active', true)->exists(), 403);

        app(TenantContext::class)->set($organization);

        try {
            return $next($request);
        } finally {
            app(TenantContext::class)->clear();
        }
    }
}
