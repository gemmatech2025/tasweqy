<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'user_id',
        'to_user_id',
        'message',
        'media',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toUser()
    {
        return $this->belongsTo(User::class , 'to_user_id');
    }


    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }


}
