<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationMemberController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $this->managedOrganization($request);
        $data = $request->validate(['email' => ['required', 'email'], 'role' => ['required', Rule::enum(OrganizationRole::class)]]);
        $user = User::where('email', $data['email'])->firstOrFail();
        $organization->members()->syncWithoutDetaching([$user->id => ['role' => $data['role'], 'is_active' => true]]);

        return back();
    }

    public function disable(Request $request, int $user): RedirectResponse
    {
        $organization = $this->managedOrganization($request);
        abort_if($user === $request->user()->id, 422, 'Cannot disable your own account.');
        $organization->members()->updateExistingPivot($user, ['is_active' => false]);

        return back();
    }

    private function managedOrganization(Request $request): Organization
    {
        $organization = Organization::findOrFail($request->session()->get('organization_id'));
        $role = $organization->roleFor($request->user());
        abort_unless(in_array($role, [OrganizationRole::OrganizationAdmin->value, OrganizationRole::SuperAdmin->value], true), 403);

        return $organization;
    }
}
