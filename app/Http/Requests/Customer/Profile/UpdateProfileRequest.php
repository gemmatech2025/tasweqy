<?php

namespace App\Http\Requests\Customer\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $user       = auth()->user();
        $customer   = $user->customer;



        if($customer){
            return [
           'gender'        => 'required|sometimes|in:male,female',
           'country_id'    => 'required|sometimes|exists:countries,id',
           'birthdate'     => 'required|sometimes|date',
        //    'phone'         => 'required|numeric',
            'phone'      => [
                'nullable',
                'numeric',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
           'code'          => 'required|sometimes|numeric',
        ];

        }


        return [
           'gender'        => 'required|in:male,female',
           'country_id'    => 'required|exists:countries,id',
           'birthdate'     => 'required|date',
        //    'phone'         => 'required|numeric',
            'phone'      => [
                'required',
                'numeric',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
           'code'          => 'required|numeric',
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
