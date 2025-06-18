<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappSession extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_sessions'; 

    protected $fillable = [
        'session_name',
        'status',
        'session_id',
        'phone_number',
        'last_qr'
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_LOGGED_OUT = 'logged_out';
    const STATUS_HAS_ISSUE = 'has_issue';
    const STATUS_QR = 'qr';

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }


}
