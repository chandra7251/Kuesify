<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (isset($data['preferences']) && is_array($data['preferences'])) {
            $currentPrefs = is_array($request->user()->preferences) ? $request->user()->preferences : [];
            $data['preferences'] = array_merge($currentPrefs, $data['preferences']);
        }

        $request->user()->fill($data);

        $emailChanged = $request->user()->isDirty('email');
        if ($emailChanged) {
            $request->user()->email_verified_at = null;
            $request->user()->sendEmailVerificationNotification();
        }

        $request->user()->save();

        if ($emailChanged) {
            return Redirect::route('verification.notice')->with('status', 'verification-link-sent');
        }

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
