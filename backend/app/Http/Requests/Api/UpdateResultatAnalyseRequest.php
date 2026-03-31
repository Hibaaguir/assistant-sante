<?php

namespace App\Http\Requests\Api;

class UpdateResultatAnalyseRequest extends StoreResultatAnalyseRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'analysis_type'   => ['sometimes', 'string', 'max:120'],
            'analysis_result' => ['sometimes', 'string', 'max:120'],
            'value'           => ['sometimes', 'numeric', 'min:0'],
            'analysis_date'   => ['sometimes', 'date'],
        ]);
    }
}
