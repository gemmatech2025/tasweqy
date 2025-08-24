<?php

namespace App\Http\Controllers\Api\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Services\UploadFilesService;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use App\Http\Controllers\BasController\BaseController;
use App\Http\Resources\Admin\Setting\PageShowResource;
use App\Http\Resources\Admin\Setting\PageIndexResource;

use App\Http\Requests\Admin\Setting\PageRequest;

class PageController extends BaseController
{


    protected const RESOURCE = PageIndexResource::class;
    protected const RESOURCE_SHOW = PageShowResource::class;
    protected const REQUEST = PageRequest::class;

    public function model()
    {
        return Page::class; 
    }


    public function getSearchableFields()
    {
        return ['title' ];
    }


    
    public function indexPaginat()
    {
        return true;
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


            $model = Page::create([
                'content'   => ['html' => $request->content],
                'title'     => $request->title, 
                'key'       => $request->key

            ]);



         

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

            if($model->title){
                $model->title   = $request->title;
            }

            if($model->content){
                $model->content = ['html' => $request->content];
            }

            $model->save();
            
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




    public function getByKey($key)
    {
        $model = Page::where('key' , $key)->first();

        if (!$model) {
            return jsonResponse(false, 404, __('messages.not_found'));
        }

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            new (static::RESOURCE_SHOW)($model)
        );
    }



    


}