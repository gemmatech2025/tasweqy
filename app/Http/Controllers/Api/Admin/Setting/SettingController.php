<?php

namespace App\Http\Controllers\Api\Admin\Setting;

use App\Http\Controllers\Controller;

use App\Models\Setting;

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
use App\Http\Requests\Admin\Setting\SettingRequest;

class SettingController extends Controller
{




    protected $uploadFilesService = null;

    public function __construct()
    {
        $this->uploadFilesService = new UploadFilesService();

    }



    public function updateSetting(SettingRequest $request)
    {
        DB::beginTransaction();
    
        try {
       
            foreach ($request->settings as $setting) {
                Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
            }
            $image_path = '';
            if ($request->hasFile('logo')) {

                $myImage = $request->file('logo');
                $image_path = $this->uploadFilesService->uploadImage($myImage, 'settings');

                $logoSetting = Setting::where('key' , 'logo')->first();
                if($logoSetting){
                    $this->uploadFilesService->deleteImage($logoSetting->value);
                    $logoSetting->value = $image_path;
                    $logoSetting->save();
                }else{
                $logoSetting = Setting::create([
                    'key' => 'logo',
                    'value' => $image_path,
                ]);
                }
            }
        DB::commit();
        return jsonResponse( true ,  200 ,__('messages.saved_success'));    
    } catch (\Exception $e) {
        DB::rollBack();
            $errorMessage = $e->getMessage();
            $errorLine = $e->getLine();
            $errorFile = $e->getFile();
            return jsonResponse(false , 500 ,__('messages.general_message') , null , null ,
            [
                'message' => $errorMessage,
                'line' => $errorLine,
                'file' => $errorFile
            ]);   
         }
    }



    public function getAllSettings()
    {
    
       
        $settings =  Setting::all()->map(function($setting){
            if($setting->key == 'logo'){
                $setting->value = asset($setting->value);
            }
            return[
                'key'   => $setting->key,
                'value' => $setting->value,
            ];
        });
        return jsonResponse( true ,  200 ,__('messages.saved_success') , $settings);    
    }
}



