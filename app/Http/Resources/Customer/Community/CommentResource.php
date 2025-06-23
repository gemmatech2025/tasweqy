<?php

namespace App\Http\Resources\Customer\Community;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PostLike;




class CommentResource extends JsonResource
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
            'comment'        => $this->comment,
            'user'           => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'image' => $this->user->image ? asset($this->user->image) : null,
                    
                    ],

            'can_edit'        => auth()->user() ? auth()->user()->id == $this->user->id ? true :false:false,




            'created_since' => $this->created_at->diffForHumans(),

            // 'created_at'     => $this->created_at,
            // 'updated_at'     => $this->updated_at,
        ];
    }
}
