<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SyncTreatmentCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checks'                   => ['required', 'array', 'min:1'],
            'checks.*.check_date'      => ['required', 'date_format:Y-m-d'],
            'checks.*.medication_key'  => ['required', 'string', 'max:120'],
            'checks.*.medication_name' => ['required', 'string', 'max:255'],
            'checks.*.dose'            => ['nullable', 'string', 'max:120'],
            'checks.*.taken'           => ['required', 'boolean'],
            'checks.*.checked_at'      => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }

    public function messages(): array
    {
        return [
            'checks.required' => 'Treatment checks list is required.',
            'checks.array' => 'Treatment checks must be an array.',
            'checks.min' => 'At least one treatment check is required.',
            'checks.*.check_date.required' => 'Check date is required.',
            'checks.*.check_date.date_format' => 'Check date must be in YYYY-MM-DD format.',
            'checks.*.medication_key.required' => 'Medication key is required.',
            'checks.*.medication_key.string' => 'Medication key must be a string.',
            'checks.*.medication_key.max' => 'Medication key must not exceed 120 characters.',
            'checks.*.medication_name.required' => 'Medication name is required.',
            'checks.*.medication_name.string' => 'Medication name must be a string.',
            'checks.*.medication_name.max' => 'Medication name must not exceed 255 characters.',
            'checks.*.dose.string' => 'Dose must be a string.',
            'checks.*.dose.max' => 'Dose must not exceed 120 characters.',
            'checks.*.taken.required' => 'Taken status is required.',
            'checks.*.taken.boolean' => 'Taken must be a boolean value (true/false).',
            'checks.*.checked_at.date_format' => 'Checked at must be in YYYY-MM-DD HH:MM:SS format.',
        ];
    }
}
