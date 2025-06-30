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
        'user_id'

    ];

    protected $casts = [
        'total_earnings' => 'decimal:2',
        'total_clients' => 'integer',
    ];

    public function socialMediaPlatform()
    {
        return $this->belongsTo(SocialMediaPlatform::class);
    }

    public function referrable()
    {
        return $this->morphTo();
    }
}
