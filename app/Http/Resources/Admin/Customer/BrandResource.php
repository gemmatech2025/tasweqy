<?php

namespace App\Http\Resources\Admin\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;



class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {


    return [
                'id'                => $this->id,
                'name'              => $this->name,
                'first_join'        => $this->first_join,

                'total_earnigns'    => $this->total_earnigns,
                'total_clients'     => $this->total_clients,
                // 'created_at'  => $this->created_at->format('F j, Y g:i A'),
            ];    
    }
}
