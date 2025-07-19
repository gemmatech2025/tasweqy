<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
class Notification extends Model
{
    use  HasTranslations;
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'image',
        'is_read',
        'read_at',
        'type',
        'payload_id'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public $translatable = ['title','body'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }


}
