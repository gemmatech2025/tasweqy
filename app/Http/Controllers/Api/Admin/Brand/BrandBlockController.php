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
use Illuminate\Support\Facades\DB;



use App\Http\Requests\Admin\Brand\BrandBlockRequest;
use App\Http\Resources\Admin\Brand\BrandBlockResource;


class BrandBlockController extends BaseController
{

    protected const RESOURCE = BrandBlockResource::class;
    protected const RESOURCE_SHOW = BrandBlockResource::class;
    protected const REQUEST = BrandBlockRequest::class;

    public function model()
    {
        return   BrandBlock::class; 
    }


    public function storeDefaultValues()
    {

        return ['creator_id' => Auth::id()];
    }




    public function MultipleChildren()
    {

        return [
            [
            'name'    => 'images' ,
            'model'   =>  BrandBlockImage::class , 
            'attr'    => [],
            'images'  => ['image'],
            'parent'  => 'brand_block_id',
            'update_scenario'  => 'delete_old' , //['delete_old' , 'update_old' ]            
            ],
        ];
    }


    
  


    public function index(Request $request)
    {
        //
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
            $excludeKeys   = $this->uploadImages();
            $baseData      = array_diff_key($validated, array_flip($excludeKeys));
            $images        = $this->uploadImageDynamically($effectiveRequest, $excludeKeys);

            $baseData = array_merge($baseData, $this->storeDefaultValues());


            $brand = Brand::find($request->brand_id);

            if($brand){
                // dd($brand->is_active);
                if($request->type == 'block'  && $brand->is_active == false){
                    return jsonResponse(false, 400, __('messages.already_blocked'));
                }else if($request->type == 'unblock' && $brand->is_active == true){
                    return jsonResponse(false, 400, __('messages.already_not_blocked'));
                }else if($request->type == 'block'){
                    $brand->is_active = false;
                }else if($request->type == 'unblock'){
                    $brand->is_active = true;
                }
                $brand->save();
            }else{
                return jsonResponse(false, 404, __('messages.brand_not_found'));
            }

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




    public function update(int $id, Request $request)
    {
        //
    }





}