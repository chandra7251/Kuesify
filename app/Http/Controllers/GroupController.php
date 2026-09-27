<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationRole;
use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->managedOrganization($request);
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        Group::create(['organization_id' => app(TenantContext::class)->id(), 'name' => $data['name']]);

        return back();
    }

    public function attachMember(Request $request, int $group): RedirectResponse
    {
        $organization = $this->managedOrganization($request);
        $group = Group::findOrFail($group);
        abort_unless($group->organization_id === $organization->id, 403);
        $data = $request->validate(['user_id' => ['required', 'integer']]);
        abort_unless($organization->members()->whereKey($data['user_id'])->exists(), 422);
        $group->members()->syncWithoutDetaching([$data['user_id']]);

        return back();
    }

    public function detachMember(Request $request, int $group, int $user): RedirectResponse
    {
        $organization = $this->managedOrganization($request);
        $group = Group::findOrFail($group);
        abort_unless($group->organization_id === $organization->id, 403);
        $group->members()->detach(User::findOrFail($user));

        return back();
    }

    private function managedOrganization(Request $request): Organization
    {
        $organization = Organization::findOrFail($request->session()->get('organization_id'));
        abort_unless(in_array($organization->roleFor($request->user()), [OrganizationRole::OrganizationAdmin->value, OrganizationRole::SuperAdmin->value], true), 403);

        return $organization;
    }
}
