<?php

namespace App\Http\Requests\Customer\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BankInfoRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */


       public function rules(): array
    {
        


        $rules = array();

        switch ($this->method()) {
            case 'POST':
                $rules +=  [
                    'iban'           => 'required|string|max:255',
                    'account_number' => 'required|string|max:255',
                    'account_name'   => 'required|string|max:255',
                    'bank_name'      => 'required|string|max:255',
                    'swift_code'     => 'required|string|max:255',
                    'address'        => 'required|string|max:255',
                ];
                break;

            case 'PATCH':
            case 'PUT':
                $rules +=  [
                    'iban'           => 'required|sometimes|string|max:255',
                    'account_number' => 'required|sometimes|string|max:255',
                    'account_name'   => 'required|sometimes|string|max:255',
                    'bank_name'      => 'required|sometimes|string|max:255',
                    'swift_code'     => 'required|sometimes|string|max:255',
                    'address'        => 'required|sometimes|string|max:255',
           
                ];

                break;
        }

        return $rules;
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
