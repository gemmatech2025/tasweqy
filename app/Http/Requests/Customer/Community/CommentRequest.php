<?php

namespace App\Http\Requests\Customer\Community;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CommentRequest extends FormRequest
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
                    'comment'           => 'required|string|max:300',
                    'post_id'           => 'required|exists:posts,id',
                ];
                break;

            case 'PATCH':
            case 'PUT':
                $rules +=  [
                    'comment'           => 'required|string|max:300',
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
