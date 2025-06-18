<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;

class RegisterRequest extends FormRequest
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
            // 'fcmToken'=>'required|string|max:255',
            // 'deviceType'=>'required|string|max:255',
            'fcmToken'   => 'nullable|required_with:deviceType|string|max:255',
            'deviceType' => 'nullable|required_with:fcmToken|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            // 'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // 'role' => 'required|in:admin,seller,user',
        ];
    }

    protected function failedValidation(Validator $validator)
{
    $errors = $validator->errors()->all();
    $response = ResponseHelper::error(__('messages.invalid_data'), $errors, 422);
    throw new HttpResponseException($response);
}
  
}
