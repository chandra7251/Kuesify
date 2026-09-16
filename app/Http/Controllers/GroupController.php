<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationRole;
use App\Models\Group;
use App\Models\Organization;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = Organization::findOrFail($request->session()->get('organization_id'));
        abort_unless(in_array($organization->roleFor($request->user()), [OrganizationRole::OrganizationAdmin->value, OrganizationRole::SuperAdmin->value], true), 403);
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        Group::create(['organization_id' => app(TenantContext::class)->id(), 'name' => $data['name']]);

        return back();
    }
}
