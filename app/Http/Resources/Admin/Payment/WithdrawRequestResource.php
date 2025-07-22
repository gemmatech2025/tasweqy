<?php

namespace App\Http\Resources\Admin\Payment;
use App\Http\Resources\Admin\Brand\BrandShowResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\TrackingEvent;

class WithdrawRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {



    // protected $fillable = ['user_id', 'total', 'status', 'withdrawable_type', 'withdrawable_id'];
    $type = '';

        if($this->withdrawable_type == 'App\Models\PaypalAccount' ){
            $type = 'paypal';
        }else if($this->withdrawable_type == 'App\Models\BankInfo ' ){
            $type = 'bank';

        }

        return [
            'id'                    => $this->id,
            'customer'              => $this->user->name ,
            'total'                 => $this->total,
            'type'                  => $type,
            'status'                => $this->status,
            'created_at'            => $this->created_at->format('F j, Y g:i A'),

        ];
    }
}
