<?php

namespace App\Http\Resources\Customer\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

       public function toArray(Request $request): array
    {



        return [
            'id'          => $this->id,
            'code'        => $this->code,
            'amount'      => $this->amount,
            'status'      => $this->status,
            'type'        => $this->type,
           
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
