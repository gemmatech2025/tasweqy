<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $fillable = ['media', 'type', 'post_id'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
