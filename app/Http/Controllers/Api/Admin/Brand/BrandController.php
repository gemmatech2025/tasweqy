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

    
}