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
        'approved',
        'approved_by',
    ];




    protected $casts = [
    'approved' => 'boolean',
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
