<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

abstract class ApiFormRequest extends FormRequest
{
    // Autoriser toutes les demandes
    public function authorize(): bool
    {
        return true;
    }
}
