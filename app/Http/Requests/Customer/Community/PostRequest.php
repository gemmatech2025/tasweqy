<?php

namespace App\Http\Requests\Customer\Community;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostRequest extends FormRequest
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
                    'content'           => 'required|string|max:300',
                    'hashtags'          => 'nullable|array',
                    'hashtags.*'        => 'required|string|max:255',
                    'media'             => 'nullable|array',
                    'media.*'           => 'required|file',                    
                ];
                break;

            case 'PATCH':
            case 'PUT':
                $rules +=  [
                    'content'           => 'required|sometimes|string|max:300',
                    'hashtags'          => 'nullable|array',
                    'hashtags.*'        => 'required|string|max:255',
                    'media'             => 'nullable|array',
                    'media.*'           => 'required|file',
           
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
