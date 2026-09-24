<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'avatar_key' => ['nullable', 'string', Rule::in(User::AVATAR_KEYS)],
            'preferences' => ['nullable', 'array'],
            'preferences.sound_effects' => ['nullable', 'boolean'],
            'preferences.reduced_motion' => ['nullable', 'boolean'],
            'preferences.leaderboard_privacy' => ['nullable', 'string', Rule::in(['real_name', 'alias', 'anonymous'])],
            'preferences.daily_streak_reminder' => ['nullable', 'boolean'],
            'preferences.show_badges_public' => ['nullable', 'boolean'],
            'preferences.question_font_size' => ['nullable', 'string', Rule::in(['normal', 'large'])],
            'preferences.time_format' => ['nullable', 'string', Rule::in(['24h', '12h'])],
        ];
    }
}
