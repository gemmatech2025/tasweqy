<?php

namespace App\Http\Controllers\Api\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use App\Models\FcmToken;
use App\Models\Customer;

use Illuminate\Support\Facades\Hash;


use Illuminate\Support\Facades\DB;

use App\Mail\CustomEmail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Services\WhatsAppOtpService;
use App\Services\UploadFilesService;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\Auth\LoginRequest;

class AuthController extends Controller
{




    public function login(LoginRequest $request)
    {
        DB::beginTransaction();
    
        try {
        $user =null;
        $credentials =null;
            $user = User::where('email' , $request->email)->first();
            if(!$user)
            {
                return jsonResponse(false , 404 ,__('messages.email_not_found') , null , null ,[]);
            }
           

            $credentials = ['email' => $request->email, 'password' => $request->password];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;
            if( $request->deviceType && $request->fcmToken){
            $fcmToken = FcmToken::where('deviceType' ,$request->deviceType)->where('user_id' , $user->id)->first();
            if($fcmToken){
            $fcmToken->fcm_token = $request->fcmToken;
            $fcmToken->save();
            }else{
                $fcm = FcmToken::create([
                    'deviceType'   => $request->deviceType,
                    'fcm_token'    => $request->fcmToken , 
                    'user_id'      => $user->id ,
                    ]);
            }   
            }


            DB::commit();
            return jsonResponse( true ,  200 ,__('messages.login_success') ,  ['user' => new UserResource($user) , 'token' => $token] );    
        }

        return jsonResponse(false , 401 ,__('messages.wrong_password') , null , null ,[]);
         
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



}