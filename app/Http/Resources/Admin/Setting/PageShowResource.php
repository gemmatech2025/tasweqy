<?php

namespace App\Http\Resources\Admin\Setting;
use App\Http\Resources\Admin\Brand\BrandShowResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\TrackingEvent;

class PageShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'key'                   => $this->key,

            'content'               => $this->content,
            'created_at'            => $this->created_at ? $this->created_at->format('F j, Y g:i A') : null,
        ];
    }
}
