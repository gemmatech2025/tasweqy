<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;

class AddNewPasswordRequest extends FormRequest
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

            'email'      => 'nullable|email|exists:users,email|required_without:phone',
            'phone'      => 'nullable|exists:users,phone|required_without:email',
            'otp'        => 'required|numeric|digits:6',
            'password'   => 'required|string|min:8|confirmed',
        
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        $response = ResponseHelper::error(__('messages.invalid_data'), $errors, 422);
        throw new HttpResponseException($response);
    }
  
}
