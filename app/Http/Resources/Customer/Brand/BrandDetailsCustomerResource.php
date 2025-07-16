<?php

namespace App\Http\Resources\Customer\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ReferralLink;
use App\Models\ReferralRequest;
use App\Models\DiscountCode;
use Illuminate\Support\Facades\Auth;

class BrandDetailsCustomerResource   extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {



        $user = Auth::user();

        $referralLinks = $this->referralLinks()
        ->whereHas('referralEarning', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['referralEarning' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }])
        ->get()
        ->map(function ($referralLink) {
        $earning = $referralLink->referralEarning;

        return [
            
                'social_media_set' => $earning?->social_media_set ?? false,
                'earning_id'       => $earning?->id ?? null,
                'total_earnings'   => $earning?->total_earnings ?? 0,
                'total_clients'    => $earning?->total_clients ?? 0,
                'socialMediaPlatform' => $earning && $earning->socialMediaPlatform
                    ? [
                        'id'   => $earning->socialMediaPlatform->id,
                        'name' => $earning->socialMediaPlatform->name
                    ]
                    : null,
                'discount_code' => [
                    'id'                 => $referralLink->id,
                    'link'               => $referralLink->link,
                    'earning_precentage' => $referralLink->earning_precentage,
                ],
            ];
        });

        $referralLinkEarnings = $referralLinks->sum(fn ($link) => $link->referralEarning->total_earnings ?? 0);
        $referralLinkCount = $referralLinks->count();


        $discountCodes = $this->discountCodes()
            ->whereHas('referralEarning', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['referralEarning' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->get()
            ->map(function ($discountCode) {
            $earning = $discountCode->referralEarning;

            return [
                'social_media_set'       => $earning?->social_media_set ?? false,
                'earning_id'       => $earning?->id ?? null,
                'total_earnings'   => $earning?->total_earnings ?? 0,
                'total_clients'    => $earning?->total_clients ?? 0,
                'socialMediaPlatform' => $earning && $earning->socialMediaPlatform
                    ? [
                        'id'   => $earning->socialMediaPlatform->id,
                        'name' => $earning->socialMediaPlatform->name
                    ]
                    : null,
                'discount_code' => [
                    'id'                 => $discountCode->id,
                    'code'               => $discountCode->code,
                    'earning_precentage' => $discountCode->earning_precentage,
                ],
            ];
        });

        $discountCodeEarnings = $discountCodes->sum(fn ($code) => $code->referralEarning->total_earnings ?? 0);
        $discountCodeCount = $discountCodes->count();
            // $table->enum('type' , ['discount_code' , 'referral_link']);



        $referralLinkRequest = ReferralRequest::where('user_id' , $user->id)
        ->where('brand_id' , $this->id)
        ->where('type' ,'referral_link')->first();
        $disountCodeRequest  = ReferralRequest::where('user_id' , $user->id)
        ->where('brand_id' , $this->id)
        ->where('type' ,'discount_code')->first();


        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'category'       => $this->category->name,
            'logo'           => $this->logo ? asset($this->logo) : null,
            'referral_link_earnings' => round($referralLinkEarnings, 2),
            'discount_code_earnings' => round($discountCodeEarnings, 2),
            'referral_links_total_marketeers' => $referralLinkCount,
            'discount_codes_total_marketeers' => $discountCodeCount,
            
            'referral_links' => $referralLinks,
            'discount_codes' => $discountCodes,

            'google_drive_url' => $this->google_drive_url,

            'referral_link_has_request' => $referralLinkRequest ? true :false,
            'discount_code_has_request' => $disountCodeRequest ? true :false,


        ];
    }
}
