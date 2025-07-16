<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SocialMediaPlatform;

class ReferralEarning extends Model
{
    protected $fillable = [
        'social_media_platform_id',
        'total_earnings',
        'referrable_type',
        'referrable_id',
        'total_clients',
        'user_id',
        'social_media_set'

    ];

    protected $casts = [
        'total_earnings' => 'decimal:2',
        'total_clients' => 'integer',
    ];

    public function socialMediaPlatform()
    {
        return $this->belongsTo(SocialMediaPlatform::class);
    }



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrable()
    {
        return $this->morphTo();
    }
}
