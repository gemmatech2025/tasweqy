<?php

namespace App\Http\Controllers\Api\Customer\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Models\PaypalAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\Customer\Payment\PayPalRequest;
use App\Http\Resources\Customer\Payment\PayPalAccountResource;

class PayPalAccountController extends BaseController
{


    protected const RESOURCE = PayPalAccountResource::class;
    protected const RESOURCE_SHOW = PayPalAccountResource::class;
    protected const REQUEST = PayPalRequest::class;

    public function model()
    {
        return   PaypalAccount::class; 
    }


    public function storeDefaultValues()
    {
        return ['user_id' => Auth::id()];
    }
















}