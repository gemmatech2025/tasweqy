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
use App\Models\User;

use App\Models\BrandCountry;
use App\Models\ReferralLink;
use App\Models\ReferralEarning;
use App\Models\DiscountCode;
use App\Models\BrandBlock;



use App\Http\Resources\Admin\Brand\BrandIndexResource;
use App\Http\Resources\Admin\Brand\BrandShowResource;
use App\Http\Requests\Admin\Brand\BrandRequest;
use App\Http\Resources\Admin\Brand\CustomerResource;
use App\Http\Resources\Admin\Brand\BrandBlockResource;



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
        $searchTerm = trim($request->input('searchTerm', ''));
        $filters = $request->input('filter', []);
        // $sortBy = $request->input('sort_by', 'id');
        // $sortOrder = $request->input('sort_order', 'asc');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $query =Brand::query();
        

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                ->orWhere('id', 'LIKE', "%{$searchTerm}%")
                ->orWhereHas('category', function ($q2) use ($searchTerm) {
                    $q2->where('name', 'LIKE', "%{$searchTerm}%");
                });
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
        $columns = \Schema::getColumnListing('brands');

        foreach ($filters as $key => $value) {
            if (in_array($key, $columns)) {
                // dd($filters);
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
            (static::RESOURCE)::collection($data),
            $pagination
        );
    }
 
    

    public function getAll()
    {
        $brands =Brand::all()->map(function ($brand){
            return ['id' => $brand->id,
            'name' => $brand->name,
            'default_link_earning' => $brand->default_link_earning,
            'default_code_earning' => $brand->default_code_earning
            ];
        });
        
        return jsonResponse(
            true,
            200,
            __('messages.success'),
            $brands
        );
    }
 
    

    public function getCustomer(Request $request , $brand_id)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $query =User::whereHas('customer')
        ->whereHas('referralEarnings' , function ($q) use($brand_id){
            $q->whereHas('referrable' ,function ($q2) use($brand_id){
            $q2->where('brand_id', $brand_id);
        });
        });


        $allData = $query->paginate($perPage, ['*'], 'page', $page); 
        $data = $allData->map(function ($user) use($brand_id){        
        $totalCodes = ReferralEarning::where('user_id' , $user->id)
        ->where('referrable_type' , 'App\\Models\\DiscountCode')
        ->whereHas('referrable' ,function ($q2) use($brand_id){
            $q2->where('brand_id', $brand_id);
        })->count();

        $totalLinks = ReferralEarning::where('user_id' , $user->id)
        ->where('referrable_type' , 'App\\Models\\ReferralLink')
        ->whereHas('referrable' ,function ($q2) use($brand_id){
            $q2->where('brand_id', $brand_id);
        })->count();

        $totalEarnings = ReferralEarning::where('user_id' , $user->id)
        ->whereHas('referrable' ,function ($q2) use($brand_id){
            $q2->where('brand_id', $brand_id);
        })->sum('total_earnings');


        // $Earnings = ReferralEarning::where('user_id' , $user->id)
        // ->whereHas('referrable' ,function ($q2) use($brand_id){
        //     $q2->where('brand_id', $brand_id);
        // })->get();


        $customer = $user->customer;



        return [
            // '$Earnings' =>$Earnings,
            'id'                        => $customer->id,
            'name'                      => $user->name,
            'total_codes'               => $totalCodes,
            'total_links'               => $totalLinks,
            'total_earnings'            => $totalEarnings,
        ]; 
        });



        $pagination = [
            'total' => $allData->total(),
            'current_page' => $allData->currentPage(),
            'per_page' => $allData->perPage(),
            'last_page' => $allData->lastPage(),
        ];

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            $data,
            $pagination
        );
    }

    public function getCodes(Request $request , $brand_id)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $query =DiscountCode::where('brand_id' , $brand_id);


        $allData = $query->paginate($perPage, ['*'], 'page', $page); 
        $data = $allData->map(function ($code) {      
            $earning = $code->referralEarning;
      
        return [
            // '$Earnings' =>$Earnings,
            'code'                        => $code->code,
            'user_name'                   => $earning ? $earning->user->name :null,
            'created_at'                  => $code->created_at?->format('F j, Y g:i A'),
            'total_clients'               => $earning ? $earning->total_clients :0,
            'status'            => $code->status,
        ]; 
        });



        $pagination = [
            'total' => $allData->total(),
            'current_page' => $allData->currentPage(),
            'per_page' => $allData->perPage(),
            'last_page' => $allData->lastPage(),
        ];

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            $data,
            $pagination
        );
    }



    public function getLinks(Request $request , $brand_id)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $query =ReferralLink::where('brand_id' , $brand_id);

        $allData = $query->paginate($perPage, ['*'], 'page', $page); 
        $data = $allData->map(function ($link) {      
        $earning = $link->referralEarning;
      
        return [
            // '$Earnings' =>$Earnings,
            'code'                        => $link->link,
            'link_code'                        => $link->link_code,

            'user_name'                   => $earning ? $earning->user->name :null,
            'created_at'                  => $link->created_at?->format('F j, Y g:i A'),
            'total_clients'               => $earning ? $earning->total_clients :0,
            'status'                      => $link->status,
        ]; 
        });



        $pagination = [
            'total' => $allData->total(),
            'current_page' => $allData->currentPage(),
            'per_page' => $allData->perPage(),
            'last_page' => $allData->lastPage(),
        ];

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            $data,
            $pagination
        );
    }


    public function getBrandBlocks(Request $request , $brand_id)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $query =BrandBlock::where('brand_id' , $brand_id);
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
            BrandBlockResource::collection($data),
            $pagination
        );
    }
}