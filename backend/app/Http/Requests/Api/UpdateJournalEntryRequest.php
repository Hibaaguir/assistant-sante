<?php

namespace App\Http\Requests\Api;

class UpdateJournalEntryRequest extends StoreJournalEntryRequest
{
    // Personnaliser les regles pour la mise a jour
    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'entry_date' => ['sometimes', 'date'],
            'tobacco' => ['sometimes', 'boolean'],
            'alcohol' => ['sometimes', 'boolean'],
        ]);
    }
}
