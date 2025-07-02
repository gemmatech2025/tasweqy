<?php

namespace App\Http\Controllers\Api\Customer\Referral;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\ReferralLink;
use App\Models\ReferralRequest;
use App\Models\DiscountCode;

use Illuminate\Support\Facades\Log;

use App\Http\Requests\Customer\Referral\ReferralRequestRequest;
use App\Http\Resources\Customer\Referral\ReferralRequestResource;

use Maatwebsite\Excel\Facades\Excel;


use App\Imports\ReferralLinksImport;
use App\Exports\ReferralLinksExportTemplate;
use Illuminate\Support\Facades\Auth;

class ReferralRequestController extends BaseController
{


    protected const RESOURCE = ReferralRequestResource::class;
    protected const RESOURCE_SHOW = ReferralRequestResource::class;
    protected const REQUEST = ReferralRequestRequest::class;

    public function model()
    {
        return   ReferralRequest::class; 
    }



    public function storeDefaultValues()
    {
        return ['user_id' => Auth::id()];
    }




      public function store(Request $request)
    {
        $reqClass      = static::REQUEST;
        $effectiveRequest = $reqClass !== Request::class
            ? app($reqClass)
            : $request;

        $validated = method_exists($effectiveRequest, 'validated')
            ? $effectiveRequest->validated()
            : $effectiveRequest->all();

        DB::beginTransaction();
        try {

            $model = ReferralRequest::where('brand_id' , $request->brand_id)
            ->where('type' , $request->type)
            ->where('user_id' , Auth::id())->first();

            if($model ){
                return jsonResponse(
                    true, 201, __('messages.add_success'),
                    new (static::RESOURCE)($model)
                );
            }

            $excludeKeys   = $this->uploadImages();
            $baseData      = array_diff_key($validated, array_flip($excludeKeys));
            $images        = $this->uploadImageDynamically($effectiveRequest, $excludeKeys);

            $baseData = array_merge($baseData, $this->storeDefaultValues());

            $model = $this->getModel()->create(array_merge($baseData, ...$images));

            $this->storeChildren($model, $effectiveRequest);

            DB::commit();

            return jsonResponse(
                true, 201, __('messages.add_success'),
                new (static::RESOURCE)($model)
            );
        }
        catch (\Throwable $e) {
            DB::rollBack();
            return jsonResponse(false, 500, __('messages.general_message'), null, null, [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }
    }



    

    public function index(Request $request)
    {

        $type = $request->input('type', '');
        $requests = ReferralRequest::where('user_id' , Auth::id());

        if($type == 'discount_code'){
            $requests->where('type' , $type);
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $data = $requests->paginate($perPage, ['*'], 'page', $page);

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