<?php

namespace App\Http\Controllers\Api\Admin\Brand;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use Illuminate\Support\Facades\Log;
use App\Services\SearchService;
use Illuminate\Support\Facades\Auth;
use App\Models\Brand;
use App\Models\BrandCountry;
use App\Models\ReferralLink;

use App\Http\Resources\Admin\Brand\BrandIndexResource;
use App\Http\Resources\Admin\Brand\BrandShowResource;
use App\Http\Requests\Admin\Brand\BrandRequest;



use App\Http\Requests\Admin\Customer\UpdateAccountApprovalRequest;


class BrandController extends BaseController
{

    protected const RESOURCE = BrandIndexResource::class;
    protected const RESOURCE_SHOW = BrandShowResource::class;
    protected const REQUEST = BrandRequest::class;

    public function model()
    {
        return   Brand::class; 
    }


    public function getSearchableFields()
    {
        return ['name' , 'description'];
    }


   public function uploadImages()
    {
       return ['logo'];
    }


    public function MultipleChildren()
    {

        return [
            [
            'name'    => 'countries' ,
            'model'   =>  BrandCountry::class , 
            'attr'    => ['country_id'],
            'images'  => [],
            'parent'  => 'brand_id',
            'update_scenario'  => 'delete_old' , //['delete_old' , 'update_old' ]            
            ],
        ];
    }


    
    public function indexPaginat()
    {
        return true;
    }




    public function getNumbers()
    {
        $totalBrands = Brand::count();
        $activeBrands = Brand::where('is_active' , true)->count();
        $inactiveBrands = Brand::where('is_active' , false)->count();
        $brandsLastMounth = Brand::whereMonth('created_at', date('m'))
        ->whereYear('created_at', date('Y'))
        ->count();

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            [
                'totalBrands' => $totalBrands,
                'activeBrands' => $activeBrands,
                'inactiveBrands' => $inactiveBrands,
                'brandsLastMounth' => $brandsLastMounth,
                // 'used_discount_codes_this_month_count' => $usedDiscountCodesThisMonthCount,
            ]
        );
    }





    public function index(Request $request)
    {

        $searchTerm = trim($request->input('search', ''));
        $filters = $request->input('filter', []);
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $query =Brand::query();
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
            (static::RESOURCE)::collection($data),
            $pagination
        );
    }
    
}