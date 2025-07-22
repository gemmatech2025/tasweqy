<?php

namespace App\Http\Resources\Admin\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'type'                      => $this->type,
            'images'                    => $this->images->map(function ($image){
                return ['image' => asset($image->image)];
            }),
            'reason'                      => $this->reason,
            'creator'                    => $this->creator->name,
            'brand'                      => $this->brand->name,
            'created_at'                 => $this->created_at?->format('F j, Y g:i A'),
        ];       
    }
}
