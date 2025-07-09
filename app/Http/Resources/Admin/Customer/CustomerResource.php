<?php

namespace App\Http\Resources\Admin\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


use App\Http\Resources\UserResource;


use App\Models\ReferralLink;
use App\Models\DiscountCode;
use App\Models\ReferralEarning;



class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {



        $referralLinkCount = ReferralEarning::where('referrable_type', ReferralLink::class)
            ->where('user_id', $this->user->id)
            ->count();

        $discountCodeCount = ReferralEarning::where('referrable_type', DiscountCode::class)
            ->where('user_id', $this->user->id)
            ->count();


        $totalEarning = ReferralEarning::where('user_id', $this->user->id)
            ->sum('total_earnings');


            $totalClients = ReferralEarning::where('user_id', $this->user->id)
            ->sum('total_clients');





        return [
            'id'          => $this->id,
            'name'        => $this->user->name,
            'country'     => $this->country? $this->country->name : null,
            'phone'       => $this->user->phone ,
            'code'        => $this->user->code ,
            'gender'      => $this->gender ,
            'status'      => $this->is_blocked ? 'blocked' : ($this->is_verified ? 'verified' : 'not_verified'),
            'referralLinkCount'      => $referralLinkCount,
            'discountCodeCount'      => $discountCodeCount,
            'totalEarning'      => $totalEarning,
            'totalClients'      => $totalClients,


        ];    
    }
}
