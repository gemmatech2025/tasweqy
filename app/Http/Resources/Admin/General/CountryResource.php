<?php

namespace App\Http\Resources\Admin\General;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name'  => $this->getTranslations('name'),
            'code' => $this->code,
            'image' => $this->image ? asset($this->image) :null ,

        ];
    }
}
