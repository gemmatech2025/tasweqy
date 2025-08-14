<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\ReferralEarning;
use Illuminate\Support\Str;

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
        $earnings= ReferralEarning::where('user_id' ,$this->id)->first();




            return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'phone'               => $this->phone,
            'code'                => $this->code,
            'email'               => $this->email,
            'image' => (Str::startsWith($this->image, ['http://', 'https://'])) ? $this->image : ($this->image ? asset($this->image) : null),

            // 'image'               => $this->image ? asset($this->image) : null,
            'locale'              => $this->locale,
            'is_phone_verified'   => $this->phone_verified_at ? true : false,
            'is_email_verified'   => $this->email_verified_at ? true : false,
            'is_notification_active'      => (bool) $this->is_notification_active,

            'is_2fa_enabled'      => (bool) $this->two_factor_secret,
            'is_2fa_confirmed'    => (bool) $this->two_factor_confirmed_at,
            'has_recovery_codes'  => (bool) $this->two_factor_recovery_codes,
            'completed_profile'   => ($customer->gender && $customer->country && $customer->birthdate) ? true : false,
            'country'       => $country ? [
                'id'        => $country->id ,
                'name'      => $country->name , 
                'code'      => $country->code , 
             ]:null,



            'birthdate' => $customer ?  $customer->birthdate ? $customer->birthdate->format('Y-m-d') : null : null,
            'gender' => $customer ?  $customer->gender : null,
            'total_balance' => $customer ?  $customer->total_balance : 0 ,
            'is_verified'   => $customer ?  $customer->is_verified : false,

            'has_referrals'   => $earnings ? true : false,


            
            // 'profile'   => $customer ?[
            //         'gender'        => $customer->gender,
            //         'total_balance' => $customer->total_balance,
            //         'is_verified'   => $customer->is_verified,
            // ] :null,

            
            // 'created_at'    => $this->created_at,
            // 'updated_at'    => $this->updated_at,
        ];
    
    
    
    
    }
}
