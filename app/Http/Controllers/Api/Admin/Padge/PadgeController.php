<?php

namespace App\Http\Controllers\Api\Admin\Padge;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Padge;
use App\Http\Resources\Admin\Padge\PadgeResource;
use App\Http\Requests\Admin\Padge\PadgeRequest;

class PadgeController extends BaseController
{

    protected const RESOURCE = PadgeResource::class;
    protected const RESOURCE_SHOW = PadgeResource::class;
    protected const REQUEST = PadgeRequest::class;

    public function model()
    {
        return   Padge::class; 
    }


    public function getSearchableFields()
    {
        return ['name' , 'description'];
    }


   public function uploadImages()
    {
       return ['image'];
    }




    
    // public function indexPaginat()
    // {
    //     return true;
    // }


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


            $lastPadge = Padge::orderByDesc('no_clients_to')->first();

            if ($lastPadge) {
                if($request->no_clients_from <= $lastPadge->no_clients_to){
                    return jsonResponse(false, 400, __('messages.conflict_in_padges_numbers'));
                }
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



    public function update(int $id, Request $request)
    {
        $reqClass      = static::REQUEST;
        $effectiveRequest = $reqClass !== Request::class
            ? app($reqClass)
            : $request;

        $validated = method_exists($effectiveRequest, 'validated')
            ? $effectiveRequest->validated()
            : $effectiveRequest->all();

        $excludeKeys = $this->uploadImages();
        $baseData    = array_diff_key($validated, array_flip($excludeKeys));
        DB::beginTransaction();
        try {


            $model = $this->getModel()->find($id);
            if (! $model) {
                return jsonResponse(false, 404, __('messages.not_found'));
            }

            if ($request->no_clients_from) {
                return jsonResponse(false, 400, __('messages.cannot_update_from_value'));
            }

            $this->updateChildren($model, $effectiveRequest);

            $images = $this->updateImageDynamically($effectiveRequest, $excludeKeys, $model);

            $model->update(array_merge($baseData, ...$images));

            DB::commit();
            return jsonResponse(
                true, 200, __('messages.update_success'),
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
    public function getHighest()
    {
        $lastPadge = Padge::orderByDesc('no_clients_to')->first();
        return jsonResponse(
                        true, 200, __('messages.update_success'),
                        ['value' => $lastPadge ? ($lastPadge->no_clients_to + 1) : 0]      
            );
    }

}