<?php

namespace App\Http\Resources\Admin\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


use App\Http\Resources\UserResource;




class CategoryShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {



        return [
            'id'    => $this->id,
            'name'  => $this->getTranslations('name'), 
            // 'name' => $this->name, 
            'image' => $this->image ? asset($this->image ) : null,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(), 
        ];    
    }
}
