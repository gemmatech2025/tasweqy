<?php

namespace App\Http\Resources\Customer\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

       public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'code'      => $this->code,
            'user'      => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ],
            'total'     => $this->total,
            'status'    => $this->status,
            'type'      => class_basename($this->withdrawable_type),
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

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
