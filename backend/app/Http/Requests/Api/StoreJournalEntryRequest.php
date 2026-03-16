<?php

// Namespace du Request dans le dossier Api
namespace App\Http\Requests\Api;

// Import de la classe FormRequest pour gérer la validation des données
use Illuminate\Foundation\Http\FormRequest;

// Import de Rule pour utiliser certaines règles avancées de validation
use Illuminate\Validation\Rule;

// Classe utilisée pour valider les données envoyées lors de la création d'une entrée du journal
class StoreJournalEntryRequest extends FormRequest
{
    // Détermine si l'utilisateur est autorisé à faire cette requête
    public function authorize(): bool
    {
        // true signifie que tous les utilisateurs autorisés peuvent envoyer cette requête
        return true;
    }

    // Règles de validation appliquées aux données envoyées dans la requête
    public function rules(): array
    {
        return [

            // Validation des informations générales liées à l'état de santé
            'entry_date' => ['required', 'date'],
            'sleep' => ['nullable', 'integer', 'min:0', 'max:24'],
            'stress' => ['nullable', 'integer', 'min:0', 'max:10'],
            'energy' => ['nullable', 'integer', 'min:0', 'max:10'],
            'sugar' => ['required', Rule::in(['low', 'medium', 'high'])],
            'caffeine' => ['nullable', 'integer', 'min:0', 'max:20'],
            'hydration' => ['nullable', 'numeric', 'min:0', 'max:20'],

            // Validation des repas consommés (tableau contenant plusieurs repas)
            'meals' => ['nullable', 'array'],
            'meals.*.type' => ['nullable', 'string', 'max:40'],
            'meals.*.label' => ['required_with:meals', 'string', 'max:255'],
            'meals.*.calories' => ['nullable', 'integer', 'min:0', 'max:15000'],

            // Validation des informations liées à l'activité physique
            'activity_type' => ['nullable', 'string', 'max:120'],
            'activity_duration' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'intensity' => ['nullable', Rule::in(['light', 'medium', 'high'])],

            // Validation des informations liées au tabac et à l'alcool
            'tobacco' => ['required', 'boolean'],
            'alcohol' => ['required', 'boolean'],
            'tobacco_types' => ['nullable', 'array'],
            'tobacco_types.cigarette' => ['nullable', 'boolean'],
            'tobacco_types.vape' => ['nullable', 'boolean'],
            'cigarettes_per_day' => ['nullable', 'integer', 'min:0', 'max:200'],
            'vape_frequency' => ['nullable', Rule::in(['Par semaine', 'Par mois'])],
            'vape_liquid_ml' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'alcohol_drinks' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}