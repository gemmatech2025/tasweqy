<?php

namespace App\Http\Controllers\Api\Admin\Dashboard;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
// use App\Services\WhatsAppOtpService;
// use App\Models\Customer;
// use Illuminate\Support\Facades\Log;
// use App\Http\Requests\Admin\General\CountryRequest;
// use App\Http\Resources\Admin\Customer\AccountVerificationRequestResource;
// use App\Services\SearchService;
// use Illuminate\Support\Facades\Auth;


use App\Models\Customer;
use App\Models\User;

use App\Models\ReferralEarning;


class DashboardController extends Controller
{
    public function dashboardInfo()
    {

        $customersCount = Customer::count();
        // $ActiveCustomersCount = Customer::whereHas('user' , )->count();
        // $activeCustomersWithReferralEarnings = Customer::whereHas('user', function ($query) {
        //     $query->whereHas('referralEarnings');
        // })->count();
        $usersWithEarnings = User::whereHas('referralEarnings')->pluck('id');

        $activeCustomersWithReferralEarnings = Customer::whereIn('user_id', $usersWithEarnings)->count();

        $notApprovedCustomers = Customer::where('is_verified', false)
            ->count();

        $approvedCustomers = Customer::where('is_verified', true)
            ->count();

        $blockedCustomers = Customer::where('is_blocked', true)
            ->count();

        $topReferral = ReferralEarning::orderByDesc('total_clients')
            ->first();



        return jsonResponse(true, 200, __('messages.success' ),  [
            'customersCount' => $customersCount,
            'activeCustomers' => $activeCustomersWithReferralEarnings,
            'notApprovedCustomers' => $notApprovedCustomers,
            'approvedCustomers' => $approvedCustomers,
            'blockedCustomers' => $blockedCustomers,
            'topReferral' => $topReferral ?  $topReferral->total_clients : 0
        ]);
    }
}