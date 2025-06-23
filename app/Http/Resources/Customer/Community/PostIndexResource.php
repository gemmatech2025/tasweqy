<?php

namespace App\Http\Resources\Customer\Community;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PostLike;




class PostIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {



        $isLiked = PostLike::where('post_id', $this->id)
                    ->where('user_id', $this->user->id)
                    ->exists();


            
                        

        return [

            'id'             => $this->id,
            'content'        => $this->content,
            'user'           => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'image' => $this->user->image ? asset($this->user->image) : null,
                    
                    ],


            'hashtags'     => $this->hashtags->map(function ($hashtag){
                return $hashtag->name;
            }),
            'medias'     => $this->medias->map(function ($media){
                return [
                    'id'        => $media->id,
                    'media'     => $media->media,
                    'type'      => $media->type,
                ];
            }),

            'seen_count'        => $this->seen_count,
            'share_count'       => $this->share_count,
            'comments_count'    => $this->comments_count,
            'likes_count'       => $this->likes_count,

            'created_since' => $this->created_at->diffForHumans(),
            'is_liked' => $isLiked,

            // 'created_at'     => $this->created_at,
            // 'updated_at'     => $this->updated_at,
        ];
    }
}
