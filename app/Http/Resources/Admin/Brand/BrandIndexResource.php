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


        $highestReferralLink = $this->referralLinks()
            ->orderByDesc('earning_precentage')
            ->first();

        $highestDiscountCode = $this->discountCodes()
            ->orderByDesc('earning_precentage')
            ->first();
           

        $ReferralLinkCount = $this->referralLinks()
            ->count();

        $DiscountCodeCount = $this->discountCodes()
            ->count();




        return [
            'id'                        => $this->id,
            'name'                      => $this->name,
            'description'               => $this->description,
            'logo'                      => $this->logo ? asset($this->logo) :null,
            'total_marketers'           => $this->total_marketers,
            'is_active'                 => $this->is_active,
            'category'                  => $this->category->name,
            'highest_referral_link'     => $highestReferralLink ? $highestReferralLink->earning_precentage : null,
            'highest_discount_code'     => $highestDiscountCode ? $highestDiscountCode->earning_precentage : null,
            'ReferralLinkCount'         => $ReferralLinkCount,
            'DiscountCodeCount'         => $DiscountCodeCount,
            'default_link_earning' => $this->default_link_earning,
            'default_code_earning' => $this->default_code_earning,

            'category'                  => $this->category->name,
            'created_at'                => $this->created_at?->format('F j, Y g:i A'),

        ];       
    }
}
