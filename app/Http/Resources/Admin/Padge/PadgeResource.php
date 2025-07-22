<?php

namespace App\Http\Resources\Admin\Padge;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PadgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'name'                      => $this->name,
            'description'               => $this->description,
            'no_clients_from'                    => $this->no_clients_from,
            'no_clients_to'                      => $this->no_clients_to,
            'image'               => $this->image ? asset($this->image) : null,
            'created_at'                 => $this->created_at?->format('F j, Y g:i A'),
        ];       
    }
}
