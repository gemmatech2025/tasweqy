<?php

namespace App\Http\Requests\Admin\Notification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PushNotificationTestingRequest extends FormRequest
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
            'user_id'                      => 'required|exists:users,id',
            // 'title'                        => 'required|array',
            // 'title.ar'                     => 'required|string|max:255',
            // 'title.en'                     => 'required|string|max:255',
            // 'body'                         => 'required|array',
            // 'body.ar'                      => 'required|string|max:255',
            // 'body.en'                      => 'required|string|max:255',
            // 'image'                        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            
            'payload'                      => 'required|numeric',
            'type'                         => ['required', Rule::in(['message','push' ,'withraw_issue' , 'withraw_success' , 'referral_link_added' , 'discount_code_added' ,'earning_added' , 'account_verified' , 'verification_rejected'])],
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
