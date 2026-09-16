<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileAvatarController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user()->avatar_key !== null) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Profile/ChooseAvatar', [
            'avatarKeys' => User::AVATAR_KEYS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'avatar_key' => ['required', 'string', Rule::in(User::AVATAR_KEYS)],
        ]);

        $request->user()->update(['avatar_key' => $data['avatar_key']]);

        return redirect()->route('dashboard');
    }
}
