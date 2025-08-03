<?php

namespace App\Http\Resources\Admin\Padge;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PadgeShowResource extends JsonResource
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
            // 'name'                      => $this->name,
            // 'description'               => $this->description,
            'name'            => [
            'en' => $this->getTranslation('name', 'en'),
            'ar' => $this->getTranslation('name', 'ar'),
            ],
            'description'     => [
                'en' => $this->getTranslation('description', 'en'),
                'ar' => $this->getTranslation('description', 'ar'),
            ],
            'no_clients_from'           => $this->no_clients_from,
            'no_clients_to'             => $this->no_clients_to,
            'image'                     => $this->image ? asset($this->image) : null,
            'created_at'                => $this->created_at?->format('F j, Y g:i A'),
        ];       
    }
}
