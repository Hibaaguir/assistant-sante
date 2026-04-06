<?php

namespace App\Http\Requests\Api;

class UpdateJournalEntryRequest extends StoreJournalEntryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // Allow partial updates
        $rules['entry_date'] = ['sometimes', 'date'];
        $rules['tobacco'] = ['sometimes', 'boolean'];
        $rules['alcohol'] = ['sometimes', 'boolean'];

        return $rules;
    }
}
