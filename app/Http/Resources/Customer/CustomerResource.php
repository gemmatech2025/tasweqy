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




            return [
            'id'            => $this->id,
            'country'       => $this->country ? [
                'id'        =>$this->country->id ,
                'name'      =>$this->country->name , 
                'code'      =>$this->country->code , 
             ]:null,
            'user'          => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'phone'     => $this->user->phone,
                'code'     => $this->user->code,


            ],
            'birthdate' => $this->birthdate ? $this->birthdate->format('Y-m-d') : null,

            'gender'        => $this->gender,
            'total_balance' => $this->total_balance,
            'is_verified'   => $this->is_verified,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    
    
    
    
    }
}
