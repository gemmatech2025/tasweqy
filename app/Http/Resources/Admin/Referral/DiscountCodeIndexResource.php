<?php

namespace App\Http\Resources\Admin\Referral;
use App\Http\Resources\Admin\Brand\BrandShowResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\TrackingEvent;

class DiscountCodeIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
            $lastEvent = TrackingEvent::where('trackable_type', 'App\Models\DiscountCode')
            ->where('trackable_id', $this->id)
            ->where('event_type', 'purchase')
            ->latest()
            ->first();

          $referralEarning = $this->referralEarning;
        return [
            'id'                    => $this->id,
            'brand'                 => $this->brand->name ,
            'earning_precentage'    => $this->earning_precentage,
            'code'                  => $this->code,
            'status'                => $this->status,
            'for_user'              => $referralEarning ? $referralEarning->user->name : null ,
            'usered_at'             => $referralEarning ? $referralEarning->created_at->format('F j, Y g:i A') : null ,
            'total_clients'         => $referralEarning ? $referralEarning->total_clients : null ,
            'created_at'            => $this->created_at ? $this->created_at->format('F j, Y g:i A') : null,
            'last_event'            => $lastEvent ? $lastEvent->created_at->format('F j, Y g:i A') :null

        ];
    }
}
