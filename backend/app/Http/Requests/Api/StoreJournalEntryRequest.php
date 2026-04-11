<?php

namespace App\Http\Requests\Api;

class StoreJournalEntryRequest extends ApiFormRequest
{
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

    public function messages(): array
    {
        return [
            'entry_date.required' => 'The entry date is required.',
            'entry_date.date' => 'The entry date must be a valid date.',
            'sleep.max' => 'Sleep hours must not exceed 24.',
            'sleep.min' => 'Sleep hours must be at least 0.',
            'stress.max' => 'Stress level must not exceed 10.',
            'stress.min' => 'Stress level must be at least 0.',
            'energy.max' => 'Energy level must not exceed 10.',
            'energy.min' => 'Energy level must be at least 0.',
            'caffeine.max' => 'Caffeine intake must not exceed 20.',
            'caffeine.min' => 'Caffeine intake must be at least 0.',
            'hydration.max' => 'Hydration must not exceed 20 liters.',
            'hydration.min' => 'Hydration must be at least 0 liters.',
            'sugar_intake.string' => 'Sugar intake must be a text value.',
            'sugar_intake.max' => 'Sugar intake must not exceed 255 characters.',
            'tobacco.required' => 'Tobacco status is required.',
            'tobacco.boolean' => 'Tobacco status must be true or false.',
            'alcohol.required' => 'Alcohol status is required.',
            'alcohol.boolean' => 'Alcohol status must be true or false.',
            'alcohol_glasses.max' => 'Alcohol glasses must not exceed 100.',
            'alcohol_glasses.min' => 'Alcohol glasses must be at least 0.',
        ];
    }
}
