<?php

namespace App\Http\Controllers\Api\Admin\General;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\Country;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\Admin\General\CountryRequest;
use App\Http\Resources\Admin\General\CountryResource;

class CountryController extends BaseController
{


    protected const RESOURCE = CountryResource::class;
    protected const RESOURCE_SHOW = CountryResource::class;
    protected const REQUEST = CountryRequest::class;

    public function model()
    {
        return   Country::class; 
    }


    public function getSearchableFields()
    {
        return ['name' , 'code'];
    }


    public function indexPaginat()
    {
        return true;
    }


    public function getAllForSellect()
    {


        $countries = Country::all();


        return jsonResponse(
        true,
        200,
        __('messages.success'),
        (static::RESOURCE)::collection($countries)
        );



    }



    

}