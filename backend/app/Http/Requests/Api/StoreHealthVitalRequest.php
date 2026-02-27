<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreHealthVitalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'measured_at' => ['nullable', 'date'],
            'heart_rate' => ['nullable', 'integer', 'min:20', 'max:260'],
            'systolic_pressure' => ['nullable', 'integer', 'min:50', 'max:300'],
            'diastolic_pressure' => ['nullable', 'integer', 'min:30', 'max:220'],
            'oxygen_saturation' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
