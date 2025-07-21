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

use App\Models\BrandBlockImage;
use App\Models\BrandBlock;



use App\Http\Requests\Admin\Brand\BrandBlockRequest;
use App\Http\Resources\Admin\Brand\BrandBlockResource;


class BrandController extends BaseController
{

    protected const RESOURCE = BrandIndexResource::class;
    protected const RESOURCE_SHOW = BrandShowResource::class;
    protected const REQUEST = BrandRequest::class;

    public function model()
    {
        return   BrandBlock::class; 
    }





    public function MultipleChildren()
    {

        return [
            [
            'name'    => 'images' ,
            'model'   =>  BrandBlockImage::class , 
            'attr'    => [],
            'images'  => ['image'],
            'parent'  => 'brand_id',
            'update_scenario'  => 'delete_old' , //['delete_old' , 'update_old' ]            
            ],
        ];
    }


    
  


    public function index(Request $request)
    {
        //
    }
 
    

   

    public function getLinks(Request $request , $brand_id)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
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
}