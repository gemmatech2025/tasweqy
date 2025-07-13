<?php

namespace App\Http\Resources\Admin\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


use App\Http\Resources\UserResource;




class UserBlockDetailsResource extends JsonResource
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
                'type'                  => $this->type,
               
                'reason'                  => $this->reason,

                'creator'                  => $this->creator ? [
                    'id'   => $this->creator->id,
                    'name' => $this->creator->name,
                ] : null,

                 'images'                => $this->images->map(function ($image){
                    return [
                        'id' => $image->id,
                        'image' => asset($image->image),
                    ];
                 }) ,


                'created_at'            => $this->created_at->format('F j, Y g:i A'),
            ];    
    
    
    }
}
