<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSignesVitauxRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'heart_rate.max' => 'La fréquence cardiaque ne doit pas dépasser 260 bpm.',
            'heart_rate.min' => 'La fréquence cardiaque doit être d\'au moins 20 bpm.',
            'systolic_pressure.max' => 'La tension systolique ne doit pas dépasser 300 mmHg.',
            'systolic_pressure.min' => 'La tension systolique doit être d\'au moins 50 mmHg.',
            'diastolic_pressure.max' => 'La tension diastolique ne doit pas dépasser 220 mmHg.',
            'diastolic_pressure.min' => 'La tension diastolique doit être d\'au moins 30 mmHg.',
            'oxygen_saturation.max' => 'La saturation en oxygène ne doit pas dépasser 100 %.',
            'oxygen_saturation.min' => 'La saturation en oxygène doit être d\'au moins 0 %.',
        ];
    }
}
