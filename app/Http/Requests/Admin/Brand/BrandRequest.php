<?php

namespace App\Http\Requests\Admin\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BrandRequest extends FormRequest
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
                        'name'                        => 'required|array',
                        'name.ar'                     => 'required|string|max:255',
                        'name.en'                     => 'required|string|max:255',
                        
                        
                        'description'                 => 'required|array',
                        'description.ar'              => 'required|string|max:255',
                        'description.en'              => 'required|string|max:255',
                        
                        'logo'                        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                        'category_id'                 => 'required|exists:categories,id',
                        // 'discount_code_earning'       => 'required|numeric|max:255',
                        // 'referral_link_earning'       => 'required|numeric|max:255',

                        'countries'                   => 'required|array',
                        'countries.*.country_id'      => 'required|exists:countries,id',


                    ];
                break;

            case 'PATCH':
            case 'PUT':
                $rules +=  [
                        'name'                        => 'required|sometimes|array',
                        'name.ar'                     => 'required|string|max:255',
                        'name.en'                     => 'required|string|max:255',
                        
                        
                        'description'                 => 'required|sometimes|array',
                        'description.ar'              => 'required|string|max:255',
                        'description.en'              => 'required|string|max:255',
                        
                        'logo'                        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                        'category_id'                 => 'required|sometimes|string|max:255',
                        'discount_code_earning'       => 'required|sometimes|numeric|max:255',
                        'referral_link_earning'       => 'required|sometimes|numeric|max:255',


                        'countries'                   => 'required|array',
                        'countries.*.country_id'      => 'required|exists:countries,id',
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
