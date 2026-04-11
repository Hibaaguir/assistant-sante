<?php

namespace App\Http\Requests\Api;

class StoreAnalysisResultRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'analysis_type' => ['required', 'string', 'max:120'],
            'result_name' => ['nullable', 'string', 'max:120'],
            'value' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:30'],
            'analysis_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'analysis_type.required' => 'The analysis type is required.',
            'analysis_type.string' => 'The analysis type must be a string.',
            'analysis_type.max' => 'The analysis type must not exceed 120 characters.',
            'result_name.string' => 'The result name must be a string.',
            'result_name.max' => 'The result name must not exceed 120 characters.',
            'value.required' => 'The value is required.',
            'value.numeric' => 'The value must be a number.',
            'value.min' => 'The value must be at least 0.',
            'unit.string' => 'The unit must be a string.',
            'unit.max' => 'The unit must not exceed 30 characters.',
            'analysis_date.required' => 'The analysis date is required.',
            'analysis_date.date' => 'The analysis date must be a valid date.',
        ];
    }
}
