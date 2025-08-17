<?php

namespace App\Http\Resources\Admin\Payment;
use App\Http\Resources\Admin\Brand\BrandShowResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\TrackingEvent;

class WithdrawRequestShowResource extends JsonResource
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
        }else if($this->withdrawable_type == 'App\Models\BankInfo' ){
            $type = 'bank';

        }

        return [
            'id'                    => $this->id,
            'customer'              => $this->user->name ,
            'code'                  => $this->code,
            'total'                 => $this->total,
            'type'                  => $type,
            'status'                => $this->status,

            'withdrawable' => $this->whenLoaded('withdrawable', function () {
                return match (class_basename($this->withdrawable_type)) {
                    'BankInfo' => [
                        'iban'           => $this->withdrawable->iban,
                        'account_number' => $this->withdrawable->account_number,
                        'account_name'   => $this->withdrawable->account_name,
                        'bank_name'      => $this->withdrawable->bank_name,
                        'swift_code'     => $this->withdrawable->swift_code,
                        'address'        => $this->withdrawable->address,
                    ],
                    'PaypalAccount' => [
                        'email' => $this->withdrawable->email,
                    ],
                    default => null,
                };
            }),

            'created_at'            => $this->created_at->format('F j, Y g:i A'),
            

        ];
    }
}
