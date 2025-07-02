<?php

namespace App\Http\Resources\Customer\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class NewBrandCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {



        $highestReferralLink = $this->referralLinks()
            ->orderByDesc('earning_precentage')
            ->first();

        $highestDiscountCode = $this->discountCodes()
            ->orderByDesc('earning_precentage')
            ->first();
           

        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'category'       => $this->category->name,
            'logo'           => $this->logo ? asset($this->logo) : null,
            'highestReferralLink'           => $highestReferralLink->earning_precentage,
            'highestDiscountCode'           => $highestDiscountCode->earning_precentage


        ];
    }
}
