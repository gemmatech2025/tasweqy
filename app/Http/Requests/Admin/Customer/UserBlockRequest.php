<?php
namespace App\Http\Requests\Admin\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
class UserBlockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $rules = array();
        switch ($this->method()) {
            case 'POST':
                $rules +=  [
                    'reason'          => 'required|string|max:1000',
                    'customer_id'     => 'required|exists:customers,id',
                    'type'            => 'required|in:block,unblock',
                    'images'          => 'required|array',
                    // 'images.*'        => 'prohibited',
                    'images.*.image'  => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ];
                break;

            case 'PATCH':
            case 'PUT':
                $rules +=  [
                    'reason' => 'required|string|max:1000',
                    // 'blocked_user_id' => 'required|exists:users,id',
                    // 'type' => 'in:block,unblock',
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
