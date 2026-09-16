<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'role' => ['required', 'string', 'in:participant,creator,organization_admin'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        [$user, $organization] = DB::transaction(function () use ($request): array {
            $role = $request->role;
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            $workspaceName = match ($role) {
                'creator' => $user->name . "'s Class & Workspace",
                'organization_admin' => $user->name . "'s Institution",
                default => $user->name . "'s Learning Space",
            };

            $organization = Organization::create([
                'name' => $workspaceName,
                'slug' => Str::slug($user->name).'-'.$user->id,
            ]);
            $organization->members()->attach($user, ['role' => $role]);

            return [$user, $organization];
        });

        event(new Registered($user));

        Auth::login($user);
        $request->session()->put('organization_id', $organization->id);

        return redirect('/choose-avatar');
    }
}
