<?php

namespace App\Http\Requests\Api;

class StoreJournalEntryRequest extends ApiFormRequest
{
    // Definir les regles de validation
    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date'],
            'sleep' => ['nullable', 'integer', 'min:0', 'max:24'],
            'stress' => ['nullable', 'integer', 'min:0', 'max:10'],
            'energy' => ['nullable', 'integer', 'min:0', 'max:10'],
            'caffeine' => ['nullable', 'integer', 'min:0', 'max:20'],
            'hydration' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'sugar_intake' => ['nullable', 'string', 'max:255'],

            'meals' => ['nullable', 'array'],
            'meals.*.meal_type' => ['nullable', 'in:breakfast,lunch,dinner,snack'],
            'meals.*.description' => ['required_with:meals', 'string', 'max:255'],
            'meals.*.calories' => ['nullable', 'integer', 'min:0', 'max:65535'],

            'activity_type' => ['nullable', 'string', 'max:120'],
            'activity_duration' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'intensity' => ['nullable', 'in:low,medium,high'],

            'tobacco' => ['required', 'boolean'],
            'alcohol' => ['required', 'boolean'],
            'tobacco_types' => ['nullable', 'array'],
            'tobacco_types.cigarette' => ['nullable', 'boolean'],
            'tobacco_types.vape' => ['nullable', 'boolean'],
            'cigarettes_per_day' => ['nullable', 'integer', 'min:0', 'max:200'],
            'vape_liquid_ml' => ['nullable', 'integer', 'min:0'],
            'alcohol_glasses' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    // Messages d'erreur en francais
    public function messages(): array
    {
        return [
            'entry_date.required' => 'La date de l\'entrée est obligatoire.',
            'entry_date.date' => 'La date de l\'entrée doit être une date valide.',
            'sleep.max' => 'Les heures de sommeil ne doivent pas dépasser 24.',
            'sleep.min' => 'Les heures de sommeil doivent être au moins 0.',
            'stress.max' => 'Le niveau de stress ne doit pas dépasser 10.',
            'stress.min' => 'Le niveau de stress doit être au moins 0.',
            'energy.max' => 'Le niveau d\'énergie ne doit pas dépasser 10.',
            'energy.min' => 'Le niveau d\'énergie doit être au moins 0.',
            'caffeine.max' => 'L\'apport en caféine ne doit pas dépasser 20.',
            'caffeine.min' => 'L\'apport en caféine doit être au moins 0.',
            'hydration.max' => 'L\'hydratation ne doit pas dépasser 20 litres.',
            'hydration.min' => 'L\'hydratation doit être au moins 0 litre.',
            'sugar_intake.string' => 'L\'apport en sucre doit être une valeur texte.',
            'sugar_intake.max' => 'L\'apport en sucre ne doit pas dépasser 255 caractères.',
            'tobacco.required' => 'Le statut du tabac est obligatoire.',
            'tobacco.boolean' => 'Le statut du tabac doit être vrai ou faux.',
            'alcohol.required' => 'Le statut de l\'alcool est obligatoire.',
            'alcohol.boolean' => 'Le statut de l\'alcool doit être vrai ou faux.',
            'alcohol_glasses.max' => 'Les verres d\'alcool ne doivent pas dépasser 100.',
            'alcohol_glasses.min' => 'Les verres d\'alcool doivent être au moins 0.',
        ];
    }
}
