<?php

namespace App\Http\Requests\Api;

class StoreAnalysisResultRequest extends ApiFormRequest
{
    // Definir les regles de validation
    public function rules(): array
    {
        return [
            'analysis_type' => ['required', 'string', 'max:120'],
            'result_name' => ['nullable', 'string', 'max:120'],
            'value' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:30'],
            'analysis_date' => ['required', 'date'],
        ];
    }

    // Messages d'erreur en francais
    public function messages(): array
    {
        return [
            'analysis_type.required' => 'Le type d\'analyse est obligatoire.',
            'analysis_type.string' => 'Le type d\'analyse doit être une chaîne de caractères.',
            'analysis_type.max' => 'Le type d\'analyse ne doit pas dépasser 120 caractères.',
            'result_name.string' => 'Le nom du résultat doit être une chaîne de caractères.',
            'result_name.max' => 'Le nom du résultat ne doit pas dépasser 120 caractères.',
            'value.required' => 'La valeur est obligatoire.',
            'value.numeric' => 'La valeur doit être un nombre.',
            'value.min' => 'La valeur doit être au moins 0.',
            'unit.string' => 'L\'unité doit être une chaîne de caractères.',
            'unit.max' => 'L\'unité ne doit pas dépasser 30 caractères.',
            'analysis_date.required' => 'La date d\'analyse est obligatoire.',
            'analysis_date.date' => 'La date d\'analyse doit être une date valide.',
        ];
    }
}
