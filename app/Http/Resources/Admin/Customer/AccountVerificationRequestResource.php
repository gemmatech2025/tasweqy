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
 return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type,
            'front_image' => asset($this->front_image),
            'back_image'  => $this->back_image ? asset($this->back_image) : null,
            'user_id'     => $this->user_id,
            'approved'    => $this->approved,
            'approved_by' => $this->approved_by,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,

            'user'        => new UserResource($this->whenLoaded('user')),
            'approver'    => new UserResource($this->whenLoaded('approver')),
        ];    }
}
