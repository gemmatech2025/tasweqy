<?php

namespace App\Http\Requests\Admin\Referral;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReferralLinkRequest extends FormRequest
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

                        'brand_id'            => 'required|exists:brands,id',
                        'link'                => 'required|url|max:255',
                        'earning_precentage'  => 'required|numeric|min:1|max:99',
                        'link_code'           => 'required|string|max:255',
                ];
                break;

            case 'PATCH':
            case 'PUT':
                $rules +=  [
                        'brand_id'            => 'required|exists:brands,id',
                        'link'                => 'required|url|max:255',
                        'earning_precentage'  => 'required|numeric|min:1|max:99',
                        'link_code'           => 'required|string|max:255',

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
