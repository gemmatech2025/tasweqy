<?php

namespace App\Http\Resources\Customer\Referral;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralRequestResource extends JsonResource
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
            'type'     => $this->type,
            'brand'     => $this->brand->name,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
