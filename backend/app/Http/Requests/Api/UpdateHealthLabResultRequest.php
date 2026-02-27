<?php

namespace App\Http\Requests\Api;

class UpdateHealthLabResultRequest extends StoreHealthLabResultRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['analysis_type'][0] = 'sometimes';
        $rules['value'][0] = 'sometimes';
        $rules['analysis_date'][0] = 'sometimes';

        return $rules;
    }
}
