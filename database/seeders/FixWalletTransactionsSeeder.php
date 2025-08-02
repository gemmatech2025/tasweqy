<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WalletTransaction;

class FixWalletTransactionsSeeder extends Seeder
{
 public function run()
    {
        // Loop through all transactions and update transatable_type and transatable_id as needed
        WalletTransaction::all()->each(function ($transaction) {
            switch ($transaction->type) {
                case 'withdraw':
                    $transaction->transatable_type = 'App\Models\WithdrawRequest';
                    $transaction->transatable_id = 2;
                    break;

                case 'referral_link':
                    // Example: use ReferralEarning if code matches a known referral transaction
                        $transaction->transatable_type = 'App\Models\ReferralLink';
                        $transaction->transatable_id = 97;
                    
                    break;

                case 'discount_code':
                    // These may not need a transatable; leaving blank
                    $transaction->transatable_type = 'App\Models\DiscountCode';
                    $transaction->transatable_id = 50;
                    break;

                default:
                    // Fallback: leave transatable info unchanged
                    break;
            }

            // Example for a unique case
            if ($transaction->code == 722006) {
                $transaction->transatable_type = 'App\Models\TrackingEvent';
                $transaction->transatable_id = 17;
            }

            $transaction->save();
        });
    }
}