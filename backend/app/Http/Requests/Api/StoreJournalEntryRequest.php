<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date'],
            'sleep' => ['nullable', 'integer', 'min:0', 'max:24'],
            'stress' => ['nullable', 'integer', 'min:0', 'max:10'],
            'energy' => ['nullable', 'integer', 'min:0', 'max:10'],
            'sugar' => ['required', Rule::in(['low', 'medium', 'high'])],
            'caffeine' => ['nullable', 'integer', 'min:0', 'max:20'],
            'hydration' => ['nullable', 'numeric', 'min:0', 'max:20'],

            'meals' => ['nullable', 'array'],
            'meals.*.type' => ['nullable', 'string', 'max:40'],
            'meals.*.label' => ['required_with:meals', 'string', 'max:255'],
            'meals.*.calories' => ['nullable', 'integer', 'min:0', 'max:15000'],

            'activity_type' => ['nullable', 'string', 'max:120'],
            'activity_duration' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'intensity' => ['nullable', Rule::in(['light', 'medium', 'high'])],

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

