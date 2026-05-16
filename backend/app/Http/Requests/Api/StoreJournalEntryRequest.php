<?php

namespace App\Http\Requests\Api;

class StoreJournalEntryRequest extends ApiFormRequest
{
    // Definir les regles de validation
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT');

        return [
            'entry_date' => $isUpdate ? ['sometimes', 'date'] : ['required', 'date'],
            'sleep' => ['nullable', 'integer', 'min:0', 'max:24'],
            'stress' => ['nullable', 'integer', 'min:0', 'max:10'],
            'energy' => ['nullable', 'integer', 'min:0', 'max:10'],
            'caffeine' => ['nullable', 'integer', 'min:0', 'max:20'],
            'hydration' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'sugar_intake' => ['required', 'in:low,medium,high'],

            'meals' => ['nullable', 'array'],
            'meals.*.meal_type' => ['nullable', 'string', 'max:30'],
            'meals.*.description' => ['required', 'string', 'max:255'],
            'meals.*.calories' => ['nullable', 'integer', 'min:0', 'max:65535'],

            'activities'                    => ['nullable', 'array'],
            'activities.*.activity_type'    => ['required', 'string', 'max:120'],
            'activities.*.activity_duration'=> ['required', 'integer', 'min:1', 'max:1440'],
            'activities.*.intensity'        => ['required', 'in:low,medium,high'],

            'tobacco' => $isUpdate ? ['sometimes', 'boolean'] : ['nullable', 'boolean'],
            'alcohol' => $isUpdate ? ['sometimes', 'boolean'] : ['nullable', 'boolean'],
            'tobacco_types' => ['required_if:tobacco,true', 'array'],
            'tobacco_types.cigarette' => ['required_without:tobacco_types.vape', 'nullable', 'boolean'],
            'tobacco_types.vape' => ['required_without:tobacco_types.cigarette', 'nullable', 'boolean'],
            'cigarettes_per_day' => ['nullable', 'required_if:tobacco_types.cigarette,true', 'integer', 'min:1', 'max:200'],
            'vape_liquid_ml' => ['nullable', 'required_if:tobacco_types.vape,true', 'integer', 'min:1'],
            'alcohol_glasses' => ['nullable', 'required_if:alcohol,true', 'integer', 'min:1', 'max:100'],
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
            'sugar_intake.required' => 'L\'apport en sucre est obligatoire.',
            'sugar_intake.in' => 'L\'apport en sucre doit être faible, modéré ou élevé.',
            'tobacco.required' => 'Le statut du tabac est obligatoire.',
            'tobacco.boolean' => 'Le statut du tabac doit être vrai ou faux.',
            'alcohol.required' => 'Le statut de l\'alcool est obligatoire.',
            'alcohol.boolean' => 'Le statut de l\'alcool doit être vrai ou faux.',
            'alcohol_glasses.required_if' => 'Le nombre de verres d\'alcool est obligatoire.',
            'alcohol_glasses.min' => 'Le nombre de verres d\'alcool doit être au moins 1.',
            'alcohol_glasses.max' => 'Le nombre de verres d\'alcool ne doit pas dépasser 100.',
            'activities.*.activity_type.required' => 'Le type d\'activité est obligatoire.',
            'activities.*.activity_duration.required' => 'La durée de l\'activité est obligatoire.',
            'activities.*.activity_duration.min' => 'La durée doit être au moins 1 minute.',
            'activities.*.intensity.required' => 'L\'intensité est obligatoire.',
            'activities.*.intensity.in' => 'L\'intensité doit être légère, modérée ou intense.',
            'meals.*.description.required' => 'La description du repas est obligatoire.',
            'cigarettes_per_day.required_if' => 'Le nombre de cigarettes par jour est obligatoire.',
            'cigarettes_per_day.integer' => 'Le nombre de cigarettes doit être un entier.',
            'cigarettes_per_day.min' => 'Le nombre de cigarettes doit être au moins 1.',
            'vape_liquid_ml.required_if' => 'Le nombre de taffes par jour est obligatoire.',
            'vape_liquid_ml.integer' => 'Le nombre de taffes doit être un entier.',
            'vape_liquid_ml.min' => 'Le nombre de taffes doit être au moins 1.',
        ];
    }
}
