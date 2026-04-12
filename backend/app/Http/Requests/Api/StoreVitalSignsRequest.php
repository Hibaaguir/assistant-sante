<?php

namespace App\Http\Requests\Api;

class StoreVitalSignsRequest extends ApiFormRequest
{
    // Definir les regles de validation
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

    // Messages d'erreur en francais
    public function messages(): array
    {
        return [
            'measured_at.required' => 'La date de mesure est obligatoire.',
            'measured_at.date' => 'La date de mesure doit être une date valide.',
            'heart_rate.max' => 'La fréquence cardiaque ne doit pas dépasser 260 bpm.',
            'heart_rate.min' => 'La fréquence cardiaque doit être au moins 20 bpm.',
            'systolic_pressure.max' => 'La pression systolique ne doit pas dépasser 300 mmHg.',
            'systolic_pressure.min' => 'La pression systolique doit être au moins 50 mmHg.',
            'diastolic_pressure.max' => 'La pression diastolique ne doit pas dépasser 220 mmHg.',
            'diastolic_pressure.min' => 'La pression diastolique doit être au moins 30 mmHg.',
            'oxygen_saturation.max' => 'La saturation en oxygène ne doit pas dépasser 100%.',
            'oxygen_saturation.min' => 'La saturation en oxygène doit être au moins 0%.',
        ];
    }
}
