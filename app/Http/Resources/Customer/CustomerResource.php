<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {



        $customer = $this->customer;
        $country = $this->customer? $this->customer->country :null;




            return [
            'id'            => $this->id,
            'name'                => $this->name,
            'phone'               => $this->phone,
            'code'                => $this->code,
            'email'               => $this->email,
            'image'               => $this->image ? asset($this->image) : null,
            'locale'              => $this->locale,
            'is_phone_verified'   => $this->phone_verified_at ? true : false,
            'is_2fa_enabled'      => (bool) $this->two_factor_secret,
            'is_2fa_confirmed'    => (bool) $this->two_factor_confirmed_at,
            'has_recovery_codes'  => (bool) $this->two_factor_recovery_codes,
            'completed_profile'   => $customer ? true : false,
            'country'       => $country ? [
                'id'        => $country->id ,
                'name'      => $country->name , 
                'code'      => $country->code , 
             ]:null,


            'profile'   => $customer ?[
                    'birthdate' => $customer->birthdate ? $customer->birthdate->format('Y-m-d') : null,
                    'gender'        => $customer->gender,
                    'total_balance' => $customer->total_balance,
                    'is_verified'   => $customer->is_verified,
            ] :null,

            
            // 'created_at'    => $this->created_at,
            // 'updated_at'    => $this->updated_at,
        ];
    
    
    
    
    }
}
