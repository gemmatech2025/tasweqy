<?php

namespace App\Http\Resources\Customer\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PostLike;




class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {


            
                        
$this->markAsRead();
        return [
            'id'             => $this->id,
            'message'        => $this->message,
            'is_mine'        => auth()->user() ? auth()->user()->id == $this->user->id ? true :false:false,
           
            'created_since' => $this->created_at->diffForHumans(),

            // 'created_at'     => $this->created_at,
            // 'updated_at'     => $this->updated_at,
        ];
    }
}
