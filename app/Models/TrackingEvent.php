<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TrackingEvent extends Model
{
    protected $fillable = [
        'trackable_type',
        'trackable_id',
        'event_type',
        'ip_address',
        'user_agent',
        'external_order_id',
    ];

    /**
     * Get the parent trackable model (e.g., ReferralLink, DiscountCode, etc.)
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }


    public function walletTransaction()
    {
        return $this->morphOne(WalletTransaction::class, 'transatable');
    }


}
