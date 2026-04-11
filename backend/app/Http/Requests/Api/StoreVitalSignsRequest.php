<?php

namespace App\Http\Requests\Api;

class StoreVitalSignsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'measured_at' => ['required', 'date'],
            'heart_rate' => ['nullable', 'integer', 'min:20', 'max:260'],
            'systolic_pressure' => ['nullable', 'integer', 'min:50', 'max:300'],
            'diastolic_pressure' => ['nullable', 'integer', 'min:30', 'max:220'],
            'oxygen_saturation' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'measured_at.required' => 'The measurement date is required.',
            'measured_at.date' => 'The measurement date must be a valid date.',
            'heart_rate.max' => 'The heart rate must not exceed 260 bpm.',
            'heart_rate.min' => 'The heart rate must be at least 20 bpm.',
            'systolic_pressure.max' => 'The systolic pressure must not exceed 300 mmHg.',
            'systolic_pressure.min' => 'The systolic pressure must be at least 50 mmHg.',
            'diastolic_pressure.max' => 'The diastolic pressure must not exceed 220 mmHg.',
            'diastolic_pressure.min' => 'The diastolic pressure must be at least 30 mmHg.',
            'oxygen_saturation.max' => 'The oxygen saturation must not exceed 100%.',
            'oxygen_saturation.min' => 'The oxygen saturation must be at least 0%.',
        ];
    }
}
