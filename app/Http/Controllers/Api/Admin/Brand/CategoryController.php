<?php

namespace App\Http\Controllers\Api\Admin\Brand;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use Illuminate\Support\Facades\Log;

use App\Services\SearchService;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\BandCountry;

use App\Http\Requests\Admin\Brand\CategoryRequest;
use App\Http\Resources\Admin\Brand\CategoryIndexResource;
use App\Http\Resources\Admin\Brand\CategoryShowResource;




class CategoryController extends BaseController
{

    protected const RESOURCE = CategoryIndexResource::class;
    protected const RESOURCE_SHOW = CategoryShowResource::class;
    protected const REQUEST = CategoryRequest::class;

    public function model()
    {
        return   Category::class; 
    }


    public function getSearchableFields()
    {
        return ['name' ];
    }


    public function uploadImages()
    {
       return ['image'];
    }
    
    
    public function indexPaginat()
    {
        return true;
    }

    public function getAllForSellect()
    {
        $categories = Category::all()->map(function($category){
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $category->image ? asset($category->image) : null,
            ];
        });


        return jsonResponse(
            true,
            200,
            __('messages.success'),
            $categories
        );
    }




    
}