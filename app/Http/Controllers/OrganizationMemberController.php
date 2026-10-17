<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\MemberInvitedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganizationMemberController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $this->managedOrganization($request);
        $data = $request->validate(['email' => ['required', 'email'], 'role' => ['required', Rule::enum(OrganizationRole::class)]]);

        $isNewUser = ! User::where('email', $data['email'])->exists();
        $temporaryPassword = $isNewUser ? str()->random(12) : null;

        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'            => explode('@', (string) $data['email'])[0],
                'password'        => $isNewUser ? bcrypt($temporaryPassword) : bcrypt(Str::random(16)),
                'avatar_key'      => 'profile_1',
            ]
        );

        $organization->members()->syncWithoutDetaching([$user->id => ['role' => $data['role'], 'is_active' => true]]);

        // Try to send invitation email; silently skip if mail is unavailable
        if ($isNewUser) {
            try {
                Notification::send($user, new MemberInvitedNotification($organization, $data['role'], $temporaryPassword));
                return back()->with('success', "Akun anggota {$user->name} berhasil dibuat dan email berisi kredensial login telah dikirim.");
            } catch (\Throwable $e) {
                // Mail failed (e.g. invalid domain, no API key, or network issue) — return success with temp password fallback
                return back()->with('success', "Akun anggota {$user->name} berhasil dibuat. Password sementara: {$temporaryPassword}");
            }
        }

        return back()->with('success', "Anggota {$user->name} berhasil ditambahkan ke organisasi.");
    }

    public function disable(Request $request, int $user): RedirectResponse
    {
        $organization = $this->managedOrganization($request);
        abort_if($user === $request->user()->id, 422, 'Cannot disable your own account.');
        abort_if(
            $organization->members()->wherePivot('role', 'super_admin')->whereKey($user)->exists(),
            403,
            'Cannot disable super admin.'
        );
        $organization->members()->updateExistingPivot($user, ['is_active' => false]);

        return back()->with('success', 'Anggota berhasil dinonaktifkan.');
    }

    private function managedOrganization(Request $request): Organization
    {
        $organization = Organization::findOrFail($request->session()->get('organization_id'));
        $role = $organization->roleFor($request->user());
        abort_unless(in_array($role, [OrganizationRole::OrganizationAdmin->value, OrganizationRole::SuperAdmin->value], true), 403);

        return $organization;
    }
}
