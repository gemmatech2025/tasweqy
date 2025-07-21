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
            'id'                        => $customer->id,
            'name'                      => $this->name,
            'total_codes'               => $totalCodes,
            'total_links'               => $totalLinks,
            'total_earnings'            => $totalEarnings,
        ];       
    }
}
