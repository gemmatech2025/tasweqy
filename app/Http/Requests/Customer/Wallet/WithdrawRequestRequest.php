<?php

namespace App\Http\Requests\Customer\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class WithdrawRequestRequest extends FormRequest
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


            'iban'           => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'account_name'   => 'nullable|string|max:255',
            'bank_name'      => 'nullable|string|max:255',
            'swift_code'     => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:255',


            'email' => 'nullable|email|max:255',
        ];

        
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');

            if ($type === 'bank') {
                $hasId = $this->filled('bank_account_id');
                $hasDetails = $this->filled('iban') && $this->filled('account_number') &&
                              $this->filled('account_name') && $this->filled('bank_name') &&
                              $this->filled('swift_code') && $this->filled('address');

                if (!($hasId || $hasDetails)) {
                    $validator->errors()->add('bank_account', __('You must provide either a bank account ID or full bank account information.'));
                }
            }

            if ($type === 'paypal') {
                $hasId = $this->filled('paypal_account_id');
                $hasEmail = $this->filled('email');

                if (!($hasId || $hasEmail)) {
                    $validator->errors()->add('paypal_account', __('You must provide either a PayPal account ID or an email.'));
                }
            }
        });
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
