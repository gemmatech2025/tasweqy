<?php

namespace App\Http\Resources\Customer\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        if(!$this->read_at){
            $this->markAsRead();
        }
        return [
                    'id'          => $this->id,
                    'title'       => $this->title,
                    'body'        => $this->body,
                    'image'       => $this->image? asset($this->image) : null,
                    'read_at'     => $this->read_at->diffForHumans(),
                    'type'        => $this->type ,
                    'payload_id'  => $this->payload_id ,
                    'created_at'  => $this->created_at->diffForHumans(),
                ]; 

       }
}
