<?php

namespace App\Http\Resources\Admin\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


use App\Http\Resources\UserResource;




class VerificationRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
 return [
    
            'id'          => $this->id,
                'code'                  => $this->code,

            'type'        => $this->type,
            'front_image' => asset($this->front_image),
            'back_image'  => $this->back_image ? asset($this->back_image) : null,
            'status'      => $this->status,
            'name'        => $this->user->name,

            'reason'      => $this->reason,
            'admin' => $this->approver ?  $this->approver->name:null,
            'created_at'  => $this->created_at->format('F j, Y g:i A'),
        ];    
    }
}
