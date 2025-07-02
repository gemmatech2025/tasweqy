<?php

namespace App\Http\Controllers\Api\Customer\Profile;

use App\Http\Controllers\Controller;


use App\Models\User;
use App\Models\Otp;
use App\Models\Customer;

use App\Models\Country;
use App\Models\AccountVerificationRequest;



use Illuminate\Support\Facades\DB;


use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\Customer\Profile\AccountVerificationRequestResource;



use Illuminate\Http\Request;
use App\Http\Requests\Customer\Profile\UpdateProfileRequest;
use App\Http\Requests\Customer\Profile\VerifyOtpRequest;
use App\Http\Requests\Customer\Profile\RequestAccountApprovalRequest;
use App\Http\Requests\Customer\Profile\UpdateLocalRequest;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Services\UploadFilesService;



class CustomerController extends Controller
{

    protected $uploadFilesService = null;
    public function __construct()
    {
        $this->uploadFilesService = new UploadFilesService();

    }





    public function updateLocale(UpdateLocalRequest $request)
    {
        $user = $request->user();
        $user->locale = $request->locale;
        $user->save();

        return jsonResponse( true ,  200 ,__('messages.data_updated_successfully'));
    }





    public function updateProfile(UpdateProfileRequest $request)
    {
        DB::beginTransaction();
    
        try {
            
            $user = Auth::user();

            $customer = Customer::where('user_id' , $user->id)->first();

            $imagePath =  $user->image;
            if ($request->hasFile('image')) {
                if($user->image){
                    $this->uploadFilesService->deleteImage($user->image);
                }
                $image = $request->file('image');
                $imagePath = $this->uploadFilesService->uploadImage($image , 'users');
            }
    

            if($customer){

                if($request->phone !=  $customer->phone ){
                    $user->phone_verified_at = null;
                }
                $customer->country_id = $request->country_id;
                $customer->birthdate = $request->birthdate;
                $customer->gender = $request->gender;
                $user->phone = $request->phone;
                $user->code = $request->code;
                $customer->save();
            }else{

                $customer = Customer::create([
                    'country_id'     => $request->country_id,
                    'birthdate'      => $request->birthdate,
                    'gender'         => $request->gender,
                    'user_id'        => $user->id,

                
                ]);
                $user->phone = $request->phone;
                $user->code = $request->code;
            }

            $user->image =$imagePath;

            $user->save();


            if(!$user->phone_verified_at){

                $result = $this->sendPhoneOtp($request->code .$request->phone , $user->id);

            DB::commit();

                if($result){
                    return jsonResponse( true ,  201 ,__('messages.data_updated_successfully_please_send_otp_on_your_whatsapp') ,
                      ['whatsapp_otp' =>true , 'customer' => new CustomerResource($user)] );    
                }

                return jsonResponse( true ,  201 ,__('messages.data_updated_successfully_but_otp_not_sent_make_sure_phone_number_correct') ,
                      ['whatsapp_otp' =>false , 'customer' => new CustomerResource($user)] );    


            }
            DB::commit();

            
            return jsonResponse( true ,  201 ,__('messages.data_updated_successfully') ,
                      ['whatsapp_otp' =>false , 'customer' => new CustomerResource($user)] );                            
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





    function verifyPhoneOtp(VerifyOtpRequest $request) {
        DB::beginTransaction();
        try {


            $user = Auth::user();
            
        
            $otp = Otp::where('user_id', $user->id)->where('type',  'phone')->first();
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

            $user->phone_verified_at = Carbon::now();
            
            $otp->delete();
            $user->save();

            $token = $user->createToken('authToken')->plainTextToken;
            DB::commit();
            return jsonResponse( true ,  200 ,__('messages.general_success')  );    

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




    public function getMyData()
    {
        $user = Auth::user();
        // $customer = Customer::where('user_id' , $user->id)->first();
        // if(!$customer){
        //     return jsonResponse( true ,  404 ,__('messages.customer_not_found_complete_profile') , null,null,[] );        
        // }                       
        return jsonResponse( true ,  200 ,__('messages.sucess') , new CustomerResource($user) );        
    }






    public function getMyApprovalRequests()
    {
        $user = Auth::user();
        $approvalRequests = AccountVerificationRequest::where('user_id' , $user->id)->get();
        return jsonResponse( true ,  200 ,__('messages.sucess') , AccountVerificationRequestResource::collection($approvalRequests) );        
    }



    public function requestApproval(RequestAccountApprovalRequest $request)
    {
        $user = Auth::user();


        $approvalRequest = AccountVerificationRequest::where('user_id' , $user->id)
        ->where('type' , $request->type)->first();


        if($approvalRequest){

            $frontImagePath = $approvalRequest->front_image;
            if ($request->hasFile('front_image')) {

            if($approvalRequest->front_image){
                $this->uploadFilesService->deleteImage($approvalRequest->front_image);
            }


                $image = $request->file('front_image');
                $frontImagePath = $this->uploadFilesService->uploadImage($image , 'account_approve_request');
            }
            $backImagePath = $approvalRequest->back_image;
            if ($request->hasFile('back_image')) {

            if($approvalRequest->back_image){
                $this->uploadFilesService->deleteImage($approvalRequest->back_image);
            }


                $image = $request->file('back_image');
                $backImagePath = $this->uploadFilesService->uploadImage($image , 'account_approve_request');
            }

            $approvalRequest->update([
                'name'           => $request->name,
                'type'           => $request->type,
                'front_image'    => $frontImagePath,
                'back_image'     => $backImagePath,
            ]);


            return jsonResponse( true ,  200 ,__('messages.updated_successfully') , new AccountVerificationRequestResource($approvalRequest) );        


        }else{

            $frontImagePath = null;
            if ($request->hasFile('front_image')) {
                $image = $request->file('front_image');
                $frontImagePath = $this->uploadFilesService->uploadImage($image , 'account_approve_request');
            }
            $backImagePath = null;
            if ($request->hasFile('back_image')) {
                $image = $request->file('back_image');
                $backImagePath = $this->uploadFilesService->uploadImage($image , 'account_approve_request');
            }
    
    


            $approvalRequest = AccountVerificationRequest::create([
                'name'           => $request->name,
                'type'           => $request->type,
                'front_image'    => $frontImagePath,
                'back_image'     => $backImagePath,
                'user_id'        => $user->id,
  
            ]);

            return jsonResponse( true ,  200 ,__('messages.created_successfully') , new AccountVerificationRequestResource($approvalRequest) );        
        }

        return jsonResponse( true ,  500 ,__('messages.general_error_message')  );        

                 
    }



        private function sendPhoneOtp($phone , $user_id) {
        // $otp_code = random_int(100000, 999999);  
        try{
        $otp_code = 123456;
        $otp= Otp::where('user_id' , $user_id)->where('type' , 'phone')->first();
        if($otp){
            $otp->delete();
        }


        $otp = Otp::create([
            'otp' =>  Hash::make($otp_code),
            'type' => 'phone',
            'otp_expires_at'=> Carbon::now()->addMinutes(5),
            'user_id'=> $user_id,
        ]);
        


        //  $result = $this->whatsAppWebService->sendWhatsappOtp(
        //             $credential,
        //             __('messages.otp_code_message') . ' ' . $verificationCode
        //         );
        //     return $result;



    return true;
    
    }catch (\Exception $e) {
    return false;


    }
    }


}