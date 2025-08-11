<?php

namespace App\Http\Resources\Admin\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Models\ReferralLink;
use App\Models\DiscountCode;
use App\Models\ReferralEarning;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class CustomerDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $locale = App::getLocale();
        $now = Carbon::now();
        $startDate = $now->copy()->subMonths(5)->startOfMonth();

        // Create a base collection with the last 6 months
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i)->startOfMonth()->locale($locale);
            $key = $date->format('Y-m');
            $months->put($key, [
                'month' => $date->translatedFormat('F'),
                'commissions' => 0,
                'clients' => 0,
            ]);
        }
// month: "يناير", commissions: 1200, clients: 45
        // Fetch referral earnings and group by month
        $referrals = ReferralEarning::where('user_id', $this->user->id)
            ->where('created_at', '>=', $startDate)
            ->get()
            ->groupBy(fn($item) => Carbon::parse($item->created_at)->format('Y-m'));

        // Update earnings and client counts
        foreach ($referrals as $monthKey => $items) {
     $months->transform(function ($value, $key) use ($monthKey, $items) {
            if ($key === $monthKey) {
                $value['commissions'] = (float) $items->sum('total_earnings');
                $value['clients'] = (int) $items->sum('total_clients');
            }
            return $value;
        });
    }


        $monthlyData = $months->values(); // Reindex

        // Optional: Frontend-ready chart data
        $chartData = [
            'labels' => $monthlyData->pluck('month'),
            'earnings' => $monthlyData->pluck('total_earnings'),
            'clients' => $monthlyData->pluck('total_clients'),
        ];


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
            'user_id'          => $this->user_id,
            'name'        => $this->user->name,
            'country'     => $this->country? $this->country->name : null,
            'phone'       => $this->user->phone ,
            'code'        => $this->user->code ,
            'gender'      => $this->gender,
            'email'       => $this->user->email,
            // 'image'       => $this->user->image ? asset($this->user->image) : null,
            'image' => (Str::startsWith($this->user->image, ['http://', 'https://'])) ? $this->user->image : ($this->user->image ? asset($this->user->image) : null),

            // 'birthdate'   => $this->birthdate,
            'birthdate' => $this->birthdate->format('F j, Y'),

            'status'      => $this->is_blocked ? 'blocked' : ($this->is_verified ? 'verified' : 'not_verified'),
            'joined_at'    => $this->user->created_at->format('F j, Y g:i A'),
            'joined_since' => $this->user->created_at->diffForHumans(),

            'referralLinkCount'      => $referralLinkCount,
            'discountCodeCount'      => $discountCodeCount,
            'totalEarning'      => $totalEarning,
            'totalClients'      => $totalClients,
            'monthlyData'        =>$monthlyData,


        ];    
    }
}
