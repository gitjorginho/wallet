<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01'
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Informe o valor do depósito.',
            'amount.numeric' => 'O valor deve ser um número válido.',
            'amount.min' => 'O valor mínimo para depósito é R$ 0,01.',
        ];
    }
}
