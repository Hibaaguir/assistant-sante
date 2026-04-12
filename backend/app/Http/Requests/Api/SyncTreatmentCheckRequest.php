<?php

namespace App\Http\Requests\Api;

class SyncTreatmentCheckRequest extends ApiFormRequest
{
    // Definir les regles de validation pour la synchronisation
    public function rules(): array
    {
        return [
            'checks'                   => ['required', 'array', 'min:1'],
            'checks.*.check_date'      => ['required', 'date_format:Y-m-d'],
            'checks.*.medication_key'  => ['required', 'string', 'max:120', 'regex:/^\d+__dose_\d+$/'],
            'checks.*.taken'           => ['required', 'boolean'],
            'checks.*.checked_at'      => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];
    }

    // Messages d'erreur en francais
    public function messages(): array
    {
        return [
            'checks.required' => 'La liste de vérification des traitements est obligatoire.',
            'checks.array' => 'Les vérifications de traitement doivent être un tableau.',
            'checks.min' => 'Au moins une vérification de traitement est obligatoire.',
            'checks.*.check_date.required' => 'La date de vérification est obligatoire.',
            'checks.*.check_date.date_format' => 'La date de vérification doit être au format AAAA-MM-JJ.',
            'checks.*.medication_key.required' => 'La clé du médicament est obligatoire.',
            'checks.*.medication_key.string' => 'La clé du médicament doit être une chaîne de caractères.',
            'checks.*.medication_key.max' => 'La clé du médicament ne doit pas dépasser 120 caractères.',
            'checks.*.medication_key.regex' => 'La clé du médicament doit correspondre au format "{idTraitement}__dose_{n}".',
            'checks.*.taken.required' => 'Le statut pris est obligatoire.',
            'checks.*.taken.boolean' => 'Le statut pris doit être une valeur booléenne (vrai/faux).',
            'checks.*.checked_at.date_format' => 'La date vérifiée doit être au format AAAA-MM-JJ HH:MM:SS.',
        ];
    }
}
