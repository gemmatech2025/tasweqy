<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FcmToken extends Model
{
    use HasFactory;

    protected $table = 'fcm_tokens';

    protected $fillable = [
        'deviceType',
        'fcm_token',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
