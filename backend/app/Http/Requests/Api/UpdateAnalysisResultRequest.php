<?php

namespace App\Http\Requests\Api;

class UpdateAnalysisResultRequest extends StoreAnalysisResultRequest
{
    // Personnaliser les regles pour la mise a jour
    public function rules(): array
    {
        return [
            'analysis_type' => ['sometimes', 'string', 'max:120'],
            'result_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'nullable', 'string', 'max:30'],
            'analysis_date' => ['sometimes', 'date'],
        ];
    }
}
