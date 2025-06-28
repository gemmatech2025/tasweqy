<?php

namespace App\Http\Controllers\Api\Admin\General;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\SocialMediaPlatform;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\Admin\General\SocialMediaPlatformRequest;
use App\Http\Resources\Admin\General\SocialMediaPlatformResource;

class SocialMediaPlatformController extends BaseController
{


    protected const RESOURCE = SocialMediaPlatformResource::class;
    protected const RESOURCE_SHOW = SocialMediaPlatformResource::class;
    protected const REQUEST = SocialMediaPlatformRequest::class;

    public function model()
    {
        return   SocialMediaPlatform::class; 
    }


    public function getSearchableFields()
    {
        return ['name'];
    }

    public function indexPaginat()
    {
        return true;
    }
    


}