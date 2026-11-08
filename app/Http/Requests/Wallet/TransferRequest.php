<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
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
        'payee_wallet_id' => 'required|exists:wallets,id',
        'amount' => 'required|numeric|min:0.01',
      ];
         
    }

   
    public function messages(): array
    {
        return [
            'payee_wallet_id.required' => 'O campo "Carteira do Destinatário" é obrigatório.',
            'payee_wallet_id.exists' => 'A carteira selecionada não existe.',
            'amount.required' => 'O campo "Valor" é obrigatório.',
            'amount.numeric' => 'O campo "Valor" deve ser um número.',
            'amount.min' => 'O valor da transferência deve ser maior que zero.',
        ];
    }
}
