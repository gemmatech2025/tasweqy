<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;

class LoginRequest extends FormRequest
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
            'email' => 'nullable|email|exists:users,email|required_without:phone',
            'phone' => 'nullable|exists:users,phone|required_without:email',
            'code' => 'nullable|numeric|required_without:email',

            'fcmToken'   => 'nullable|required_with:deviceType|string|max:255',
            'deviceType' => 'nullable|required_with:fcmToken|string|max:255',
            'password' => 'required|string|min:8',

        ];
    }


    protected function failedValidation(Validator $validator)
{
    $errors = $validator->errors()->all();
    $response = ResponseHelper::error(__('messages.invalid_data'), $errors, 422);
    throw new HttpResponseException($response);
}
  
}
