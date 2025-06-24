<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'image'];

    public $translatable = ['name'];

    public function bands()
    {
        return $this->hasMany(Band::class, 'category_id');
    }
}
