<?php

namespace App\Http\Requests\Customer\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateWithdrawRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'    => 'required|in:bank,paypal',
            'total'   => 'required|numeric|min:1',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'paypal_account_id' => 'nullable|exists:paypal_accounts,id',
        ];


        
    }



    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $flatErrors = collect($errors)->map(function ($messages) {
            return $messages[0];
        });

        throw new HttpResponseException(
            jsonResponse(false, 422, __('messages.validation_error'), null, null, $flatErrors)
        );
    }
}
