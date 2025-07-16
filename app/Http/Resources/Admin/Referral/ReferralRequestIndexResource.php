<?php

namespace App\Http\Resources\Admin\Referral;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralRequestIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

       public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'type'     => $this->type,
            'brand'    => $this->brand->name,
            'user'     => $this->user->name,

            'created_at' => $this->created_at?->format('F j, Y g:i A'),
        ];
    }
}
