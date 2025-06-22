<?php

namespace App\Http\Resources\Customer\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankInfoIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'             => $this->id,
            // 'iban'           => $this->iban,
            'account_number' => $this->account_number,
            'account_name'   => $this->account_name,
            'is_default'     => (bool) $this->is_default,

            // 'bank_name'      => $this->bank_name,
            // 'swift_code'     => $this->swift_code,
            // 'address'        => $this->address,
            // 'is_default'     => (bool) $this->is_default,
            // 'user_id'        => $this->user_id,
            // 'created_at'     => $this->created_at,
            // 'updated_at'     => $this->updated_at,
        ];
    }
}
