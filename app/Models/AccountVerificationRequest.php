<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountVerificationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'front_image',
        'back_image',
        'user_id',
        'reason',
        'status',
        'approved_by',
        'code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
