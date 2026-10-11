<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    public function switch(Request $request, Organization $organization): RedirectResponse
    {
        abort_unless($request->user()->organizations()->whereKey($organization)->exists(), 403);

        $request->session()->put('organization_id', $organization->id);

        return back();
    }

    public function update(Request $request): RedirectResponse
    {
        $organization = Organization::findOrFail($request->session()->get('organization_id'));
        $role = $organization->roleFor($request->user());
        abort_unless(in_array($role, [OrganizationRole::OrganizationAdmin->value, OrganizationRole::SuperAdmin->value], true), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'timezone' => ['nullable', 'string', 'max:50'],
        ]);

        $organization->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . $organization->id,
            'timezone' => $data['timezone'] ?? $organization->timezone,
        ]);

        return back()->with('success', 'Identitas dan nama organisasi berhasil diperbarui.');
    }
}
