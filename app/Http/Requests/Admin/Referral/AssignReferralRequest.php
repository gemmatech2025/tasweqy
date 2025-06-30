<?php

namespace App\Http\Requests\Admin\Referral;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AssignReferralRequest extends FormRequest
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
        


        

        return [
            'referral_request_id'      => 'required|exists:referral_requests,id',

            'type'                     => ['required', Rule::in(['discount_code', 'referral_link'])],

            'discount_code_id'         =>[
                'nullable',
                'exists:discount_codes,id',
                Rule::requiredIf($this->input('type') === 'discount_code'),
        ],
            'referral_link_id'          => [
                'nullable',
                'exists:referral_links,id',
                Rule::requiredIf($this->input('type') === 'referral_link'),
        ],


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
