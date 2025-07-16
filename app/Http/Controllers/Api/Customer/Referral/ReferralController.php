<?php

namespace App\Http\Controllers\Api\Customer\Referral;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\ReferralLink;
use App\Models\ReferralRequest;
use App\Models\DiscountCode;

use Illuminate\Support\Facades\Log;

use App\Http\Requests\Customer\Referral\ReferralRequestRequest;
use App\Http\Resources\Customer\Referral\ReferralRequestResource;
use App\Http\Resources\Customer\Referral\ReferralEarningResource;

use Maatwebsite\Excel\Facades\Excel;


use App\Imports\ReferralLinksImport;
use App\Exports\ReferralLinksExportTemplate;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{


   
    public function getAllMyreferralLinks(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        $user = Auth::user();

        $referrals = ReferralEarning::where('user_id' , $user->id)
        ->where('referrable_type' , ReferralLink::class);

        $data = $query->paginate($perPage, ['*'], 'page', $page);

        $pagination = [
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ];

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            ReferralEarningResource::collection($data),
            $pagination
        );
    }

}