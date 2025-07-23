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
use App\Services\FirebaseService;


use App\Http\Resources\Admin\Referral\ReferralRequestIndexResource;
use App\Http\Requests\Admin\Referral\AssignReferralRequest;
use App\Http\Resources\Admin\Referral\DiscountCodeIndexResource;
use App\Http\Resources\Admin\Referral\ReferralRequestShowResource;



class ReferralRequestController extends Controller
{

    protected $searchService = null;
    protected $firebaseService = null;

    public function __construct()
    {
        $this->searchService = new SearchService();
        $this->firebaseService = new FirebaseService();

    }



    public function index(Request $request)
    {

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);



        $searchTerm = trim($request->input('searchTerm', ''));
        $filters = $request->input('filter', []);

        $query =  ReferralRequest::query();

        if ($searchTerm) {
            $query->where('id', 'LIKE', "%$searchTerm%")
            ->orWhereHas('user', function ($innerQuery) use ($searchTerm) {
                    $innerQuery->where('name', 'LIKE', "%$searchTerm%");
            })
            ->orWhereHas('brand', function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%$searchTerm%");
            });
        }


                $filters = array_map(function ($value) {
        if (is_string($value)) {
                $lower = strtolower($value);
                return match ($lower) {
                    'true' => 1,
                    'false' => 0,
                    default => is_numeric($value) ? $value + 0 : $value,
                };
            }
            return $value;
        }, $filters);

        $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');
        $columns = \Schema::getColumnListing('referral_requests');

        foreach ($filters as $key => $value) {
            if (in_array($key, $columns)) {
                $query->where($key, $value);
            }
        }






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
   



    public function assignReferralToCustomer(AssignReferralRequest $request)
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

            $this->firebaseService->handelNotification($referralRequest->user, 'discount_code_added' , $discountCode->id );

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
            $this->firebaseService->handelNotification($referralRequest->user, 'referral_link_added' , $referralLink->id );


       //     'referral_link_added',
        //     'discount_code_added',
        //     'earning_added',
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