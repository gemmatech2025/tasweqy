<?php

namespace App\Http\Requests\Admin\Referral;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReferralLinkListRequest extends FormRequest
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
                'brand_id'                    => 'required|exists:brands,id',
                'links'                       => 'required|array',
                'links.*.link'                => 'required|url|max:255',
                'links.*.earning_precentage'  => 'required|numeric|min:1|max:99',
                'links.*.link_code'           => 'required|string|max:255',
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
