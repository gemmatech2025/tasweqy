<?php

namespace App\Http\Resources\Admin\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


use App\Http\Resources\UserResource;




class AccountVerificationRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

    $customer = $this->user->customer ? $this->user->customer : null;
    $country = $this->user->customer ? $this->user->customer->country : null;
    return [
                'id'                    => $this->id,
                'name_in_request'       => $this->name,
                'name'                  => $this->user->name,
                'country'               => $country ? $country->name : null,
                'front_image'           => asset($this->front_image),
                'back_image'            => $this->back_image ? asset($this->back_image) : null,
                'phone'                 => $this->user->phone,
                'code'                  => $this->user->code,
                'email'                 => $this->user->email ,
                'status'                => $this->status,
                'reason'                => $this->reason,
                'type'        => $this->type,

                'admin' => $this->whenLoaded('approver', function () {
                    return [
                            'id'   => $this->approver->id,
                            'name' => $this->approver->name,
                        ];
                    }),


                'created_at'            => $this->created_at->format('F j, Y g:i A'),
            ];    
    
    
    }
}
