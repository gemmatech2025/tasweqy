<?php

namespace App\Http\Resources\Admin\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'description'       => $this->description,
            'logo'              => $this->logo ? asset($this->logo) :null,
            'total_marketers'   => $this->total_marketers,

            'category'          => $this->category->name,
            // 'countries'         => $this->countries->map(function($country){
            //     return ['country_id' => $country->id];
            // }),

            'created_at'        => $this->created_at?->toDateTimeString(),
            'updated_at'        => $this->updated_at?->toDateTimeString(),
        ];      }
}
