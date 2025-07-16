<?php

namespace App\Http\Controllers\Api\Customer\Brand;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Brand;
use App\Models\ReferralLink;
use App\Models\ReferralRequest;
use App\Models\DiscountCode;
use App\Models\ReferralEarning;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


use App\Http\Resources\Customer\Brand\NewBrandCardResource;
use App\Http\Resources\Customer\Brand\CustomerBrandCardResource;

use App\Http\Resources\Customer\Brand\BrandDetailsResource;
use App\Http\Resources\Customer\Brand\BrandDetailsCustomerResource;



class BrandController extends Controller
{



    public function getNewBrands(Request $request)
    {

        $user = Auth::user();
        $customer = $user->customer;
        $userId = $user->id;

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
      
        $orderBy = $request->input('orderBy', 'latest_created'); // marketeers - earning
        $countryId = $request->input('country_id', '');
        $categoryId = $request->input('category_id', '');

        $finalCountryId = $countryId ?? ($customer?->country_id);

        $brands = Brand::withSum([
            'referralLinks as total_referral_earning' => fn($q) => $q->has('referralEarning'),
            'discountCodes as total_discount_earning' => fn($q) => $q->has('referralEarning'),
        ], 'earning_precentage')
        ->withCount([
            'referralLinks as active_referral_count' => fn($q) => $q->whereHas('referralEarning'),
            'discountCodes as active_discount_count' => fn($q) => $q->whereHas('referralEarning'),
        ])
        ->whereHas('referralLinks', function ($query) use ($userId) {
            $query->whereDoesntHave('referralEarning', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        })
        ->whereHas('discountCodes', function ($query) use ($userId) {
            $query->whereDoesntHave('referralEarning', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }); 



        if($finalCountryId){
            $brands->whereHas('countries', function ($query) use ($finalCountryId) {
                $query->where('countries.id', $finalCountryId);
            });
        }


        if ($categoryId) {
        $brands->where('category_id', $categoryId);
        }


        switch ($orderBy) {
        case 'earning':
            $brands->orderByDesc(DB::raw('COALESCE(total_referral_earning, 0) + COALESCE(total_discount_earning, 0)'));
            break;

        case 'marketeers':
            $brands->orderByDesc(DB::raw('active_referral_count + active_discount_count'));
            break;

        case 'latest_created':
        default:
            $brands->orderByDesc('created_at');
            break;
        }

        $data = $brands->paginate($perPage, ['*'], 'page', $page);

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
            NewBrandCardResource::collection($data),
            $pagination
        );


    }




    public function getMyBrands(Request $request)
    {

        $user = Auth::user();
        $customer = $user->customer;
        $userId = $user->id;

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
      
        $orderBy = $request->input('orderBy', 'latest_created'); // marketeers - earning
        $countryId = $request->input('country_id', '');
        $categoryId = $request->input('category_id', '');

        $finalCountryId = $countryId ?? ($customer?->country_id);

        $brands = Brand::withSum([
            'referralLinks as total_referral_earning' => fn($q) => $q->has('referralEarning'),
            'discountCodes as total_discount_earning' => fn($q) => $q->has('referralEarning'),
        ], 'earning_precentage')
        ->withCount([
            'referralLinks as active_referral_count' => fn($q) => $q->whereHas('referralEarning'),
            'discountCodes as active_discount_count' => fn($q) => $q->whereHas('referralEarning'),
        ])
        ->whereHas('referralLinks', function ($query) use ($userId) {
            $query->whereHas('referralEarning', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        })
        ->orWhereHas('discountCodes', function ($query) use ($userId) {
            $query->whereHas('referralEarning', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        })->orWhereHas('referralRequests', function ($query) use ($userId) {
            $query->where('user_id', $userId);  
        }); 



        if($finalCountryId){
            $brands->whereHas('countries', function ($query) use ($finalCountryId) {
                $query->where('countries.id', $finalCountryId);
            });
        }


        if ($categoryId) {
        $brands->where('category_id', $categoryId);
        }


        switch ($orderBy) {
        case 'earning':
            $brands->orderByDesc(DB::raw('COALESCE(total_referral_earning, 0) + COALESCE(total_discount_earning, 0)'));
            break;

        case 'marketeers':
            $brands->orderByDesc(DB::raw('active_referral_count + active_discount_count'));
            break;

        case 'latest_created':
        default:
            $brands->orderByDesc('created_at');
            break;
        }

        $data = $brands->paginate($perPage, ['*'], 'page', $page);

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
            CustomerBrandCardResource::collection($data),
            $pagination
        );


    }




    public function getBrandById($brand_id)
    {


        $user = Auth::user();

        $brand = Brand::find($brand_id);

        if (!$brand) {
            return jsonResponse(false, 404, __('messages.not_found'));
        }



         $hasReferralEarning = ReferralEarning::where('user_id', $user->id)
        ->whereHasMorph(
            'referrable',
            [ReferralLink::class, DiscountCode::class],
            function ($query) use ($brand_id) {
                $query->where('brand_id', $brand_id);
            }
        )
        ->exists();


        $hasReferralRequest = ReferralRequest::where('user_id', $user->id)
        ->where('brand_id', $brand_id)
        ->exists();


        if($hasReferralEarning || $hasReferralRequest){
        
        
        return jsonResponse(
            true,
            200,
            __('messages.success'),
            new BrandDetailsCustomerResource($brand),
        );

        }




        return jsonResponse(
            true,
            200,
            __('messages.success'),
            new BrandDetailsResource($brand),
        );


    }



    public function addSocialMediaPlatform($earning_id , $platform_id)
    {
        $referralEarning = ReferralEarning::find($earning_id);
        if (!$referralEarning) {
            return jsonResponse(false, 404, __('messages.not_found'));
        }

        if($platform_id != 'none'){
            $referralEarning->social_media_platform_id = $platform_id;
        }

        $referralEarning->social_media_set = true;
        $referralEarning->save();

        return jsonResponse(
            true,
            200,
            __('messages.success'),
        );
    }

}