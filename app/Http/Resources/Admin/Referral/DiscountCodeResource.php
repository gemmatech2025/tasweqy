<?php

namespace App\Http\Resources\Admin\Referral;
use App\Http\Resources\Admin\Brand\BrandShowResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountCodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'brand' => new BrandShowResource($this->whenLoaded('brand')),

            'earning_precentage' => $this->earning_precentage,
            'link' => $this->link,

        ];
    }
}
