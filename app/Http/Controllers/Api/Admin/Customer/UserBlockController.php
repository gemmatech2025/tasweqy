<?php

namespace App\Http\Controllers\Api\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use Illuminate\Support\Facades\Log;

use App\Services\SearchService;
use Illuminate\Support\Facades\Auth;
use App\Models\UserBlock;
use App\Models\UserBlockImage;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\Admin\Customer\UserBlockRequest;
use App\Http\Resources\Admin\Customer\UserBlockDetailsResource;
use App\Http\Resources\Admin\Customer\UserBlockIndexResource;

class UserBlockController extends BaseController
{
    protected const RESOURCE = UserBlockIndexResource::class;
    protected const RESOURCE_SHOW = UserBlockDetailsResource::class;
    protected const REQUEST = UserBlockRequest::class;

    public function model()
    {
        return   UserBlock::class; 
    }


    // public function getSearchableFields()
    // {
    //     return ['name' ];
    // }


    // public function uploadImages()
    // {
    //    return ['image'];
    // }
    




    public function MultipleChildren()
    {

        return [
            [
            'name'    => 'images' ,
            'model'   => UserBlockImage::class , 
            'attr'    => [],
            'images'  => ['image'],
            'parent'  => 'user_block_id',
            'update_scenario'  => 'delete_old' , //['delete_old' , 'update_old' ]
            ]
        ];
    }


    
    public function indexPaginat()
    {
        return true;
    }



    public function storeDefaultValues()
    {

        return ['creator_id' => Auth::id() ];
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


            $customer = Customer::find($validated['customer_id']);
            if (!$customer) {
                return jsonResponse(false, 404, __('messages.not_found'));
            }

            if($request->type == 'unblock' && $customer->is_blocked == false) {
                return jsonResponse(false, 422, __('messages.user_already_unblocked'));
            } elseif ($request->type == 'block' && $customer->is_blocked == true) {
                return jsonResponse(false, 422, __('messages.user_already_blocked'));
            }




            $excludeKeys   = $this->uploadImages();
            $baseData      = array_diff_key($validated, array_flip($excludeKeys));
            $images        = $this->uploadImageDynamically($effectiveRequest, $excludeKeys);

            $baseData = array_merge($baseData, $this->storeDefaultValues());

            $model = $this->getModel()->create(array_merge($baseData, ...$images));

            $this->storeChildren($model, $effectiveRequest);
            // dd($request->type);
            if($request->type == 'unblock') {
                $customer->update(['is_blocked' => false]);
            } else {
                $customer->update(['is_blocked' => true]);
            }

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




    public function getCustomersBlocks(Request $request , $id)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        $customer = Customer::find($id); 
        
        if(!$customer){
            return jsonResponse(false, 404, __('messages.not_found'));
        }      



        $query = UserBlock::where('customer_id', $id);

        $data = $query->paginate($perPage, ['*'], 'page', $page);

        $pagination = [
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ];

        return jsonResponse(true, 200, __('messages.success'), UserBlockIndexResource::collection($data), $pagination);
    }



    
}