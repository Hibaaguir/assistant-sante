<?php

namespace App\Http\Requests\Api;

class UpdateJournalEntryRequest extends StoreJournalEntryRequest
{
    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'entry_date' => ['sometimes', 'date'],
            'tobacco' => ['sometimes', 'boolean'],
            'alcohol' => ['sometimes', 'boolean'],
        ]);
    }
}
