<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HashtagPost extends Model
{
    protected $table = 'hashtag_posts';

    protected $fillable = ['post_id', 'hashtag_id'];
}
