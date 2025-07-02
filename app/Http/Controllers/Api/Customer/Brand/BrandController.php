<?php

namespace App\Http\Controllers\Api\Customer\Brand;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Brand;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


use App\Http\Resources\Customer\Brand\NewBrandCardResource;
use App\Http\Resources\Customer\Brand\CustomerBrandCardResource;


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
        ->whereHas('discountCodes', function ($query) use ($userId) {
            $query->whereHas('referralEarning', function ($q) use ($userId) {
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
            CustomerBrandCardResource::collection($data),
            $pagination
        );


    }




}