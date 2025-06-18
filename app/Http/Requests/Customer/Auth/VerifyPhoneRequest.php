<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;

class VerifyPhoneRequest extends FormRequest
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

        // dd($this->all()); 
        return [
           'phone' => 'required|string|exists:users,phone',
        ];
    }


    protected function failedValidation(Validator $validator)
{
    $errors = $validator->errors()->all();
    $response = ResponseHelper::error(__('messages.invalid_data'), $errors, 422);
    throw new HttpResponseException($response);
}
  
}
