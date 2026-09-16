<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['multiple_choice', 'true_false', 'fill_blank', 'essay'])],
            'prompt' => ['required', 'string', 'max:4000'],
            'options' => ['nullable', 'array', 'required_if:type,multiple_choice', 'min:2'],
            'options.*' => ['string', 'max:500', 'distinct'],
            'correct_answer' => ['nullable', 'string', 'max:4000', 'required_unless:type,essay'],
            'points' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50', 'distinct'],
        ];
    }
}
