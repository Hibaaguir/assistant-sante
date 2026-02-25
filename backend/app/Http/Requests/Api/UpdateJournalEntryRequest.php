<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class UpdateJournalEntryRequest extends StoreJournalEntryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['entry_date'] = ['sometimes', 'date'];
        $rules['sugar'] = ['sometimes', Rule::in(['low', 'medium', 'high'])];
        $rules['tobacco'] = ['sometimes', 'boolean'];
        $rules['alcohol'] = ['sometimes', 'boolean'];

        return $rules;
    }
}
