<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone_number' => ['sometimes', 'required', 'string', 'max:20', Rule::unique(User::class)->ignore($this->user()->id)],
        ];

    }

    public function validationData(): array
    {
        // Tests patch with email; your UI patches with phone_number.
        return $this->all();
    }

    public function passedValidation(): void
    {
        // Keep email_verified_at logic handled by the controller/form flow.
    }
}

