<?php

namespace App\Http\Requests\Admin\Padge;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PadgeRequest extends FormRequest
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
                     
                        'image'                       => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                        'no_clients_from'             => 'required|numeric|lt:no_clients_to',
                        'no_clients_to'               => 'required|numeric',

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

                        // 'no_clients_from'             => 'required|sometimes|numeric|lt:no_clients_to',
                        'no_clients_to'               => 'required|sometimes|numeric',
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
