<?php

namespace App\Http\Resources\Admin\Referral;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralRequestShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

       public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'type'      => $this->type,
            'brand'     => ['id' => $this->brand->id,'name' => $this->brand->name],
            'user'      => ['id' => $this->user->id,'name' => $this->user->name],

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
