<?php

namespace App\Http\Controllers\Api\Admin\Referral;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\ReferralLink;
use App\Models\ReferralRequest;
use App\Models\DiscountCode;

use Illuminate\Support\Facades\Log;

use App\Services\SearchService;
use App\Http\Resources\Admin\Referral\ReferralRequestIndexResource;
use App\Http\Requests\Admin\Referral\AssignReferralRequest;
use App\Http\Resources\Admin\Referral\DiscountCodeIndexResource;
use App\Http\Resources\Admin\Referral\ReferralRequestShowResource;



class ReferralRequestController extends Controller
{

    protected $searchService = null;
    public function __construct()
    {
        $this->searchService = new SearchService();
    }



    public function index(Request $request)
    {

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        $query =  ReferralRequest::query();
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
            ReferralRequestIndexResource::collection($data),
            $pagination
        );
    }
   



    public function assifnReferralToCustomer(AssignReferralRequest $request)
    {



        DB::beginTransaction();
        try {


            $referralRequest =  ReferralRequest::find($request->referral_request_id);
            if($request->type == 'discount_code'){
                $discountCode = DiscountCode::find($request->discount_code_id);
                if($discountCode->isReserved()){
                    return jsonResponse(false, 500, __('messages.referal_is_reserved'));
                }
                

                $discountCode->referralEarning()->create([
                    'total_earnings' => 0,
                    'total_clients'  => 0,
                    'user_id'        => $referralRequest->user_id,
                ]);


            } else if($request->type == 'referral_link'){
                $referralLink = ReferralLink::find($request->referral_link_id);
                if($referralLink->isReserved()){
                    return jsonResponse(false, 500, __('messages.referal_is_reserved'));
                }

                $referralLink->referralEarning()->create([
                    'total_earnings' => 0,
                    'total_clients'  => 0,
                    'user_id'        => $referralRequest->user_id,
                ]);


            }else {
                 return jsonResponse(false, 500, __('messages.wrong_type'));
        }
            

            $referralRequest->delete();

           
            DB::commit();

            return jsonResponse(
                true, 200, __('messages.success'),
            );
        }
        catch (\Throwable $e) {
            DB::rollBack();
            return jsonResponse(false, 500, __('messages.general_message'), null, null, [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }

    }








    public function getReferralLinks(Request $request)
    {
        $brand_id = $request->input('brand_id', '');
        $links = ReferralLink::doesntHave('referralEarning');
        if($brand_id){
            $links->where('brand_id' ,$brand_id);
        }
        return jsonResponse(
            true, 200, __('messages.success'),
            ReferralLinkIndexResource::collection($links->get())
        );
    }



    public function getDiscountCodes(Request $request)
    {

        $brand_id = $request->input('brand_id', '');
        $links = DiscountCode::doesntHave('referralEarning');
        if($brand_id){
            $links->where('brand_id' ,$brand_id);
        }
        return jsonResponse(
            true, 200, __('messages.success'),
            DiscountCodeIndexResource::collection($links->get())
        );

    }
   
    public function show($id)
    {

        $request = ReferralRequest::find($id);
        if(!$request){
            return jsonResponse(false, 404, __('messages.not_found'));
        }
        return jsonResponse(
            true, 200, __('messages.success'),
            new ReferralRequestShowResource($request)
        );

    }







    public function getNumbers()
    {
        $totalCount = ReferralRequest::count();

        $referralLinkRequestsCount = ReferralRequest::where('type' , 'referral_link')->count();
        $discountCodeRequestsCount = ReferralRequest::where('type' , 'discount_code')->count();
        $topBrandLinks = ReferralRequest::select('brand_id')
        ->where('type' , 'referral_link')
        ->selectRaw('COUNT(*) as request_count')
        ->groupBy('brand_id')
        ->orderByDesc('request_count')
        ->first();
        $topBrandCodes = ReferralRequest::select('brand_id')
        ->where('type' , 'discount_code')
        ->selectRaw('COUNT(*) as request_count')
        ->groupBy('brand_id')
        ->orderByDesc('request_count')
        ->first();

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            [
                'totalCount' => $totalCount,
                'referralLinkRequestsCount' => $referralLinkRequestsCount,
                'discountCodeRequestsCount' => $discountCodeRequestsCount,
                'topBrandLinks' => $topBrandLinks ? $topBrandLinks->brand->name:'',
                'topBrandCodes' =>  $topBrandCodes ? $topBrandCodes->brand->name:'',
            ]
        );
    }
   



}