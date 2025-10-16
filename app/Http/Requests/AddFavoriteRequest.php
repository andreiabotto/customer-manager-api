<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // O middleware de auth já verifica a autenticação
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|min:1'
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Product ID is required.',
            'product_id.integer' => 'Product ID must be an integer.',
            'product_id.min' => 'Product ID must be greater than zero.'
        ];
    }
}