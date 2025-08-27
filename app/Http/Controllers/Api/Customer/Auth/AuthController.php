<?php

namespace App\Http\Controllers\Api\Customer\Auth;

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
use App\Http\Requests\Customer\Auth\LoginRequest;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use App\Http\Requests\Customer\Auth\VerifyEmailRequest;
use App\Http\Requests\Customer\Auth\VerifyOtpRequest;
use App\Http\Requests\Customer\Auth\VerifyPhoneRequest;
use App\Http\Requests\Customer\Auth\ForgetPassword;
use App\Http\Requests\Customer\Auth\AddNewPasswordRequest;
use App\Http\Requests\Customer\Auth\SendOtpRequest;
use Illuminate\Support\Str;



class AuthController extends Controller
{

    // function jsonResponse($status, $code, $message = null, $data = null, $meta = null, $errors = null)



    protected $whatsAppWebService = null;
    protected $uploadFilesService = null;

    public function __construct()
    {
        $this->whatsAppWebService = new WhatsAppOtpService();
        $this->uploadFilesService = new UploadFilesService();

    }

    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
    
        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $this->uploadFilesService->uploadImage($image , 'users');
            }
    
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'image' => $imagePath,
                'role' => 'customer',  
                'password' => Hash::make($request->password), 
            ]);

            $customer = Customer::create([
                'user_id'        => $user->id,                
            ]);


            if( $request->deviceType && $request->fcmToken){
            $fcm = FcmToken::create([
                            'deviceType'   => $request->deviceType,
                            'fcm_token'    => $request->fcmToken , 
                            'user_id'      => $user->id ,
                            ]);
            }

            $result = $this->sendEmailOtp($user->email , $user->id);
            DB::commit();

            if($result){
                return jsonResponse( true ,  201 ,__('messages.user_created_success') ,  new UserResource($user) );    
            }

            return jsonResponse( true ,  201 ,__('messages.user_created_but_problem_in_otp_try_resend') ,  new UserResource($user) );    
  
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



    public function login(LoginRequest $request)
    {
        DB::beginTransaction();
    
        try {
        $user =null;
        $credentials =null;
        if($request->email){
            $user = User::where('email' , $request->email)->first();
            if(!$user)
            {
                return jsonResponse(false , 404 ,__('messages.email_not_found') , null , null ,[]);
            }
            if(!$user->email_verified_at){
                return jsonResponse(false , 400 ,__('messages.email_not_verified') , null , null ,[]);
            }

            $credentials = ['email' => $request->email, 'password' => $request->password];


        }else if($request->phone){
        $user = User::where('phone' , $request->phone)->where('code' , $request->code)->first();
        if(!$user)
        {
            return jsonResponse(false , 404 ,__('messages.phone_not_found') , null , null ,[]);
        }

        if(!$user->phone_verified_at){
            return jsonResponse(false , 400 ,__('messages.phone_not_verified') , null , null ,[]);
        }

        $credentials = ['phone' => $request->phone, 'password' => $request->password];

        } 


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




    public function sendOtp(SendOtpRequest $request) {
        DB::beginTransaction();
    
        try {

            if($request->email){

                $user = User::where('email' , $request->email)->first();
        if(!$user)
        {
            return jsonResponse(false , 404 ,__('messages.email_not_found') , null , null ,[]);
        }
        if($user->email_verified_at){
            return jsonResponse(true , 200 ,__('messages.already_verified') , null , null ,[]);

        }
        $result = $this->sendEmailOtp($user->email , $user->id);

        if($result){
            DB::commit();
            return jsonResponse(true , 200 ,__('messages.general_success') , null , null ,[]);
        }

        }else if($request->phone){


        $user = User::where('phone' , $request->phone)->where('code' , $request->code)->first();
        if(!$user)
        {
            return jsonResponse(false , 404 ,__('messages.phone_not_found') , null , null ,[]);

        }
        if($user->phone_verified_at){
            return jsonResponse(true , 200 ,__('messages.already_verified') , null , null ,[]);

        }


        $result = $this->sendPhoneOtp($request->code . $request->phone  , $user->id);

        if($result){
            DB::commit();
            return jsonResponse(true , 200 ,__('messages.general_success') , null , null ,[]);
        }
        }
        
        return jsonResponse(false , 500 ,__('messages.problem_sending_otp') , null , null , []);   


        
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




    function verifyOtp(VerifyOtpRequest $request) {
        DB::beginTransaction();
        try {
            $user = null;
            $type ='';
            if($request->email){
            
                $user = User::where('email' , $request->email)->first();
                if(!$user)
                {
                    return jsonResponse(false , 404 ,__('messages.email_not_found') , null , null ,[]);
                }
                if($user->email_verified_at){
                    return jsonResponse(true , 200 ,__('messages.already_verified') , null , null ,[]);
                }

               
                $type='email';
    
    
            }else if($request->phone){
            $user = User::where('phone' , $request->phone)->where('code' , $request->code)->first();
            if(!$user)
            {
                return jsonResponse(false , 404 ,__('messages.phone_not_found') , null , null ,[]);

            }
    
            if($user->phone_verified_at){
                return jsonResponse(true , 200 ,__('messages.already_verified') , null , null ,[]);
            }
    
            $type='phone';
            } 

        
            $otp = Otp::where('user_id', $user->id)->where('type',  $type)->first();
            if (!$otp) {
                return jsonResponse(false , 400 ,__('messages.otp_not_found') , null , null ,[]);
            }
      
            if (Carbon::now()->gt($otp->otp_expires_at)) {
                return jsonResponse(false , 401 ,__('messages.otp_expired') , null , null ,[]);
            }
    
            if (!Hash::check($request->otp, $otp->otp)) {
                DB::rollBack();
                return jsonResponse(false , 400 ,__('messages.wrong_otp') , null , null ,[]);
            }


    
            if ($request->phone) {
                $user->phone_verified_at = Carbon::now();
            } else if ($request->email) {
                $user->email_verified_at = Carbon::now();
            }
    
            $otp->delete();
            $user->save();

            $token = $user->createToken('authToken')->plainTextToken;
            DB::commit();
            // return ResponseHelper::success(['user' => new UserResource($user) , 'token' => $token]);
            return jsonResponse( true ,  200 ,__('messages.general_success') ,  ['user' => new UserResource($user) , 'token' => $token] );    

    
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
    
















    public function sendForgetPasswordOtp(SendOtpRequest $request) {
        DB::beginTransaction();
        try {
            if($request->email){
                $user = User::where('email' , $request->email)->first();
                if(!$user)
                {
                    return jsonResponse(false , 404 ,__('messages.email_not_found') , null , null ,[]);
                }
                // if(!$user->email_verified_at){
                //     return jsonResponse(false , 400 ,__('messages.email_not_verified') , null , null ,[]);
                // }    
    
            }else if($request->phone){
            $user = User::where('phone' , $request->phone)->first();
            if(!$user)
            {
                return jsonResponse(false , 404 ,__('messages.phone_not_found') , null , null ,[]);

            }
    
            // if(!$user->phone_verified_at){
            //     return jsonResponse(false , 400 ,__('messages.phone_not_verified') , null , null ,[]);
            // }    
            } 




        $result =false;
        if($request->email){              
         $result = $this->sendEmailOtp($user->email , $user->id , 'forget');
        }else if($request->phone){


        $phoneNumber = preg_replace('/[+\s]/', '', $request->code . $request->phone);


        $result = $this->sendPhoneOtp($phoneNumber  , $user->id , 'forget');
        } 

        if($result){
            DB::commit();
            return jsonResponse(true , 200 ,__('messages.general_success') , null , null ,[]);
        }

        return jsonResponse(false , 500 ,__('messages.problem_sending_otp') , null , null , []);   

          
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





    function verifyOtpForgetPassword(VerifyOtpRequest $request) {
        DB::beginTransaction();
        
        try {

            $otp =null;
             if($request->email){
                $user = User::where('email' , $request->email)->first();
                if(!$user)
                {
                    return jsonResponse(false , 404 ,__('messages.email_not_found') , null , null ,[]);
                }
                // if(!$user->email_verified_at){
                //     return jsonResponse(false , 400 ,__('messages.email_not_verified') , null , null ,[]);
                // }    


                $otp = Otp::where('user_id', $user->id)->where('type', 'forgot_password_email')->first();
                
            }else if($request->phone){
            $user = User::where('phone' , $request->phone)->first();
            if(!$user)
            {
                return jsonResponse(false , 404 ,__('messages.phone_not_found') , null , null ,[]);

            }
    
            // if(!$user->phone_verified_at){
            //     return jsonResponse(false , 400 ,__('messages.phone_not_verified') , null , null ,[]);
            // }   

            $otp = Otp::where('user_id', $user->id)->where('type', 'forgot_password_phone')->first();
        

            } 



            if (!$otp) {
                return jsonResponse(false , 404 ,__('messages.otp_not_found') , null , null ,[]);
            }
            if (Carbon::now()->gt($otp->otp_expires_at)) {
                return jsonResponse(false , 401 ,__('messages.otp_expired') , null , null ,[]);
            }
    
            if (!Hash::check($request->otp, $otp->otp)) {
                return jsonResponse(false , 400 ,__('messages.wrong_otp') , null , null ,[]);
            }




            $token = Str::random(64);
            $otp->otp =  Hash::make($token);
            $otp->otp_expires_at = Carbon::now()->addMinutes(5);
            $otp->save();


            DB::commit();
            return jsonResponse(true , 200 ,__('messages.corrrect_otp_code') , ['token' => $token ] , null ,[]);
    
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











    function addNewPasswordForgetPassword(AddNewPasswordRequest $request) {
        DB::beginTransaction();
        
        try {

            $otp =null;
             if($request->email){
                $user = User::where('email' , $request->email)->first();
                if(!$user)
                {
                    return jsonResponse(false , 404 ,__('messages.email_not_found') , null , null ,[]);
                }
                // if(!$user->email_verified_at){
                //     return jsonResponse(false , 400 ,__('messages.email_not_verified') , null , null ,[]);
                // }    


                $otp = Otp::where('user_id', $user->id)->where('type', 'forgot_password_email')->first();
                
            }else if($request->phone){
            $user = User::where('phone' , $request->phone)->first();
            if(!$user)
            {
                return jsonResponse(false , 404 ,__('messages.phone_not_found') , null , null ,[]);

            }
    
            // if(!$user->phone_verified_at){
            //     return jsonResponse(false , 400 ,__('messages.phone_not_verified') , null , null ,[]);
            // }   

            $otp = Otp::where('user_id', $user->id)->where('type', 'forgot_password_phone')->first();
        

            } 



            if (!$otp) {
                return jsonResponse(false , 404 ,__('messages.otp_not_found') , null , null ,[]);
            }
            if (Carbon::now()->gt($otp->otp_expires_at)) {
                return jsonResponse(false , 401 ,__('messages.otp_expired') , null , null ,[]);
            }
    
            if (!Hash::check($request->token, $otp->otp)) {
                return jsonResponse(false , 400 ,__('messages.wrong_otp') , null , null ,[]);
            }


            $user->password = Hash::make($request->password);
            $user->save();
            $otp->delete();

            DB::commit();
            return jsonResponse(true , 200 ,__('messages.password_changed_successfully') , null , null ,[]);
    
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













    

 





    public function deleteProfile()
    {
        DB::beginTransaction();
    
        try {
            
            $user = Auth::user();
            if(!$user)
            {
                return jsonResponse(false , 404 ,__('messages.user_not_found') , null , null ,[]);
            }

            $customer = Customer::where('user_id' , $user->id)->first();
            if($customer ){
                $customer->delete();
            }
            if($user->image){
                $this->uploadFilesService->deleteImage($user->image);
            }
            $user->delete();
            DB::commit();

            return jsonResponse(true , 200 ,__('messages.user_deleted_successfully') , null , null ,[]);

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








    public function logout()
    {
        try{
        Auth::user()->currentAccessToken()->delete();

        return jsonResponse(true , 200 ,__('messages.logged_out_successfully') , null , null ,[]);
        } catch (\Exception $e) {

    

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




    public function changeOldPassword(Request $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:8',
            'password'     => 'required|string|min:8|confirmed',
            ]);
    
        if ($validator->fails()) {
            return jsonResponse(false , 422 ,__('messages.invalid_data') , null , null ,$validator->errors());
        }
        
        try {
            $user = Auth::user();
            if(!$user)
            {
                return jsonResponse(false , 404 ,__('messages.user_not_found') , null , null ,[]);

            }

            if (!Hash::check($request->old_password, $user->password)) {
                return jsonResponse(false , 403 ,__('messages.old_password_incorrect') , null , null ,[]);

            }

            $user->password = Hash::make($request->password);
            $user->save();
            DB::commit();

        return jsonResponse(true , 200 ,__('messages.password_updated_successfully') , null , null ,[]);
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





        private function sendEmailOtp($email , $user_id , $type = 'verify') {
        // $otp_code = random_int(100000, 999999);  
        try{
        $otp_code = 123456;
        $otpType = '';


       if ($type == 'verify') {
            $otpType = 'email';

        } elseif ($type == 'forget') {

            $otpType = 'forgot_password_email';
        } else {
            return false;
        }

        Otp::where('user_id', $user_id)->where('type', $otpType)->delete();

        $otp = Otp::create([
            'otp' =>  Hash::make($otp_code),
            'type' => $otpType,
            'otp_expires_at'=> Carbon::now()->addMinutes(5),
            'user_id'=> $user_id,
        ]);
        


        $subject = "Welcome to Our Platform";
        $body = "Thank you for joining us! We're excited to have you on board.\n your otp is ". $otp_code;
    
        Mail::to($email)->send(new CustomEmail($subject, $body));

    return true;
    
    }catch (\Exception $e) {
    return false;
    }
    }




        public function sendPhoneOtp($phone , $user_id ,$type = 'verify') {
        // $otp_code = random_int(100000, 999999);  
        try{
        $otp_code = 123456;
        $otpType = '';

        if ($type == 'verify') {
            $otpType = 'phone';
        } elseif ($type == 'forget') {
            $otpType = 'forgot_password_phone';
        } else {
            return false;
        }

        // $otp = Otp::where('user_id' , $user_id)->
        // where('type' , $otpType)->delete();

        $otp = Otp::where('user_id', $user_id )->where('type', $otpType)->delete();


        // if($otp){
        //     $otp->otp_expires_at = Carbon::now()->addMinutes(5);
        //     $otp->otp = Hash::make($otp_code);
        //     $otp->save();
        // }else{

        // }

        $otp = Otp::create([
            'otp' =>  Hash::make($otp_code),
            'type' => $otpType,
            'otp_expires_at'=> Carbon::now()->addMinutes(5),
            'user_id'=> $user_id,
        ]);
        


         $result = $this->whatsAppWebService->sendWhatsappOtp(
                    $phone,
                    __('messages.otp_code_message') . ' ' . $otp_code
                );
            return $result;

    return true;
    
    }catch (\Exception $e) {
    return false;
    }
    }

}