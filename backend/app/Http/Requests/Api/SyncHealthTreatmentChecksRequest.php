<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SyncHealthTreatmentChecksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checks' => ['required', 'array', 'min:1'],
            'checks.*.check_date' => ['required', 'date'],
            'checks.*.medication_key' => ['required', 'string', 'max:120'],
            'checks.*.medication_name' => ['required', 'string', 'max:255'],
            'checks.*.dose' => ['nullable', 'string', 'max:120'],
            'checks.*.taken' => ['required', 'boolean'],
            'checks.*.checked_at' => ['nullable', 'date'],
        ];
    }
}
