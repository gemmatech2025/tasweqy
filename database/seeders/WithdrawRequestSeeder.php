<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PaypalAccount;
use App\Models\WithdrawRequest;
use App\Models\AccountVerificationRequest;

use App\Models\BankInfo;
use Illuminate\Support\Facades\DB;

class WithdrawRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

         AccountVerificationRequest::truncate();

        // Loop over all users
        User::all()->each(function ($user) {

        AccountVerificationRequest::create([
            'name'        => 'John Doe',
            'type'        => 'passport',
            'front_image' => 'uploads/verification/passport_front.jpg',
            'back_image'  => 'uploads/verification/passport_back.jpg',
            'user_id'     => $user->id,
            'reason'      => 'To access full account features',
            'status'      => 'pending', // could be pending/approved/rejected
            'approved_by' => null,
            'code'        => $this->generateCode(),
        ]);

        AccountVerificationRequest::create([
            'name'        => 'Jane Smith',
            'type'        => 'id',
            'front_image' => 'uploads/verification/id_front.jpg',
            'back_image'  => 'uploads/verification/id_back.jpg',
            'user_id'     => $user->id,
            'reason'      => 'Identity verification',
            'status'      => 'approved',
            'approved_by' => $user->id,
            'code'        => $this->generateCode(),
        ]);
        });
            
    //         // Ensure the user has a PayPal account
    //         $paypalAccount = PaypalAccount::where('user_id', $user->id)->first();
    //         if (!$paypalAccount) {
    //             $paypalAccount = PaypalAccount::create([
    //                 'email'      => $user->email ?? $user->name . '@paypal.com',
    //                 'is_default' => true,
    //                 'user_id'    => $user->id,
    //             ]);

    //         BankInfo::where('user_id', $user->id)->update(['is_default' => false]);


                


    //         }

    //         // Create a withdraw request
    //         WithdrawRequest::create([
    //             'user_id'           => $user->id,
    //             'total'             => rand(50, 500), // fake amount
    //             'status'            => 'pending',
    //             'withdrawable_type' => PaypalAccount::class,
    //             'withdrawable_id'   => $paypalAccount->id,
    //             'code'              => $this->generateCode(),
    //         ]);



    //          $bankInfo = BankInfo::where('user_id', $user->id)->first();
    //         if (!$bankInfo) {
    //             $bankInfo = BankInfo::create([
    // 'iban'           => 'EG' . $this->randomNumericString(20),
    //                 'account_number' => rand(10000000, 99999999),
    //                 'account_name'   => $user->name ?? 'Unknown User',
    //                 'bank_name'      => 'Test Bank',
    //                 'swift_code'     => 'TESTEGCXXXX',
    //                 'address'        => 'Test Bank Street, Egypt',
    //                 'is_default'     => false,
    //                 'user_id'        => $user->id,
    //             ]);
    //         }

    //         // Create a withdraw request for BankInfo
    //         WithdrawRequest::create([
    //             'user_id'           => $user->id,
    //             'total'             => rand(50, 500),
    //             'status'            => 'pending',
    //             'withdrawable_type' => BankInfo::class,
    //             'withdrawable_id'   => $bankInfo->id,
    //             'code'              => $this->generateCode(),
    //         ]);
        
    //     });


        


        
    }



// Helper to generate a random numeric string of any length
private function randomNumericString(int $length): string
{
    $digits = '';
    for ($i = 0; $i < $length; $i++) {
        $digits .= random_int(0, 9);
    }
    return $digits;
}

    public function generateCode()
    {
        $code = random_int(100000, 999999); 
        while (WithdrawRequest::where('code', $code)->exists()) {
            $code = random_int(100000, 999999); 
        }
        return $code;

    }

}
