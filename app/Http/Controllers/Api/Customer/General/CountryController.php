<?php

namespace App\Http\Controllers\Api\Customer\General;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\Country;
use Illuminate\Support\Facades\Log;

// use App\Http\Requests\Admin\General\CountryRequest;
// use App\Http\Resources\Admin\General\CountryResource;

class CountryController extends Controller
{




    public function getAllForSellect()
    {


        $countries = Country::all()->map(function($country){
            return['id' => $country->id , 'name' => $country->name , 'code' => $country->code , 'image' => $country->image ? asset($country->image) :null ,];
        });


        return jsonResponse(
        true,
        200,
        __('messages.success'),
        $countries
        );
    }



    

}