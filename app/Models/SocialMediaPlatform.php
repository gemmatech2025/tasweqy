<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SocialMediaPlatform extends Model
{
    use HasTranslations;

    protected $fillable = ['logo', 'name'];

    public $translatable = ['name'];

    public $timestamps = false; 
}
