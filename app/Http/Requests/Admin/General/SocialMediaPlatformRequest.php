<?php

namespace App\Http\Requests\Admin\General;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SocialMediaPlatformRequest extends FormRequest
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
                    'logo'                        => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ];
                break;

            case 'PATCH':
            case 'PUT':
                $rules +=  [
                    'name'                        => 'required|array',
                    'name.ar'                     => 'required|string|max:255',
                    'name.en'                     => 'required|string|max:255',
                    'logo'                        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
