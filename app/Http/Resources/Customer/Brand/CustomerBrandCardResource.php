<?php

namespace App\Http\Resources\Customer\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CustomerBrandCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = Auth::user();

        $referralLinkEarnings = $this->referralLinks()
            ->whereHas('referralEarning', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['referralEarning' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->get()
            ->sum(fn ($link) => $link->referralEarning->total_earnings ?? 0);

        $discountCodeEarnings = $this->discountCodes()
            ->whereHas('referralEarning', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['referralEarning' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->get()
            ->sum(fn ($code) => $code->referralEarning->total_earnings ?? 0);

        return [
            'id'                     => $this->id,
            'name'                   => $this->name,
            'description'            => $this->description,
            'category'               => $this->category->name,
            'logo'                   => $this->logo ? asset($this->logo) : null,
            'referral_link_earnings' => round($referralLinkEarnings, 2),
            'discount_code_earnings' => round($discountCodeEarnings, 2),

        ];
    }
}
