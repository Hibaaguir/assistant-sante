<?php

namespace App\Http\Requests\Api;

class StoreAnalysisResultRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'analysis_type' => ['sometimes', 'required', 'string', 'max:120'],
            'result_name'   => ['nullable', 'string', 'max:120'],
            'value'         => ['nullable', 'numeric', 'min:0'],
            'unit'          => ['nullable', 'string', 'max:30'],
            'description'   => ['nullable', 'string'],
            'analysis_date' => ['sometimes', 'required', 'date'],
        ];
    }
}
