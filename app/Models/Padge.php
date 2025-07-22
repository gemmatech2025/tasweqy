<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Padge extends Model
{
    use HasTranslations;
    protected $table = 'padges';

    protected $fillable = [
        'name',
        'description',
        'no_clients_from',
        'no_clients_to',
        'image',
    ];

    public $translatable = ['name', 'description'];

    // protected $casts = [
    //     'name' => 'array',
    //     'description' => 'array',
    // ];
}
