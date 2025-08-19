<?php

namespace App\Http\Controllers\Api\Customer\Profile;

use App\Http\Controllers\Controller;


use App\Models\User;
use App\Models\Otp;
use App\Models\Customer;
use App\Models\Setting;

use App\Models\Country;
use App\Models\AccountVerificationRequest;

use App\Models\ReferralLink;
use App\Models\DiscountCode;
use App\Models\ReferralEarning;



use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Resources\Customer\Referral\ReferralEarningResource;

use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\Customer\Profile\AccountVerificationRequestResource;



use Illuminate\Http\Request;
use App\Http\Requests\Customer\Profile\UpdateProfileRequest;
use App\Http\Requests\Customer\Profile\VerifyOtpRequest;
use App\Http\Requests\Customer\Profile\RequestAccountApprovalRequest;
use App\Http\Requests\Customer\Profile\UpdateLocalRequest;
use App\Http\Requests\Customer\Profile\VerifyVersionRequest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Services\UploadFilesService;
use App\Services\FirebaseService;


class CustomerController extends Controller
{

    protected $uploadFilesService = null;
    protected $firebaseService = null;

    public function __construct()
    {
        $this->uploadFilesService = new UploadFilesService();
        $this->firebaseService = new FirebaseService();

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
            \Log::error('Error sending notification', [
                'user' => $user,
                'request' => $request->all(),
            ]);

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

                if ($request->phone && $request->phone != $customer->phone) {
                    $user->phone_verified_at = null; 
                    $user->phone = $request->phone;
                    $user->code = $request->code ?? $user->code;
                }

                $customerData = $request->only('country_id', 'birthdate', 'gender', 'phone', 'code');
                $customer->update($customerData);
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


            if($request->name ){
                $user->name = $request->name;
            }



            $user->image =$imagePath;
            $user->save();            

             if(!$user->phone_verified_at){
                $result = $this->sendPhoneOtp($request->code .$request->phone , $user->id);
                $otpMessage = $result
                    ? __('messages.data_updated_successfully_please_send_otp_on_your_whatsapp')
                    : __('messages.data_updated_successfully_but_otp_not_sent_make_sure_phone_number_correct');
                $otpFlag = $result;
            } else {
                $otpMessage = __('messages.data_updated_successfully');
                $otpFlag = false;
            }

            DB::commit();
            $user->load('customer');

                return jsonResponse(true, 201, $otpMessage, [
                        'whatsapp_otp' => $otpFlag,
                        'customer' => new CustomerResource($user)
                    ]);                          
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




    public function toggelNotification()
    {
        $user = Auth::user();
        
        $user->is_notification_active = !$user->is_notification_active;
        $user->save();
                      
        return jsonResponse( true ,  200 ,__('messages.sucess') ,  [ 'is_notification_active' => $user->is_notification_active] );        
    }




    public function getMyData()
    {
        $user = Auth::user();                   
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
        ->where('type' , $request->type)
        ->first();

        $customer = $user->customer;
        if(!$customer){
            return jsonResponse( false ,  400 ,__('messages.complete_profile_first')  );        
        }


        if($customer->is_verified){
            return jsonResponse( false ,  400 ,__('messages.already_verified')  );        
        }


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
                'approved'       => '0'
           ]);

            $this->firebaseService->sendAdminNotification('verification_request_added', $approvalRequest->id ,  $user);

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


            $code = $this->generateCode();
            $approvalRequest = AccountVerificationRequest::create([
                'code'           => $code,
                'name'           => $request->name,
                'type'           => $request->type,
                'front_image'    => $frontImagePath,
                'back_image'     => $backImagePath,
                'user_id'        => $user->id,
  
            ]);
            $this->firebaseService->sendAdminNotification('verification_request_added', $approvalRequest->id ,  $user);
            return jsonResponse( true ,  200 ,__('messages.created_successfully') , new AccountVerificationRequestResource($approvalRequest) );        
        }

        return jsonResponse( false ,  500 ,__('messages.general_error_message')  );        

                 
    }


    public function generateCode()
    {
        $code = random_int(100000, 999999); 
        while (WithdrawRequest::where('code', $code)->exists()) {
            $code = random_int(100000, 999999); 
        }
        return $code;

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








    public function homeInfo()
    {
        $user = Auth::user();


        $customer = $user->customer;

        if(!$customer){
            return jsonResponse( false ,  400 ,__('messages.complete_profile_first')  );        
        }


        // $referralLink = ReferralEarning::where('user_id', $user->id)
        //     ->where('referrable_type', ReferralLink::class)
        //     ->whereHas('referrable')

        //     ->orderBy('total_earnings', 'desc')
        //     ->first();


        // $descountcode = ReferralEarning::where('user_id', $user->id)
        //     ->where('referrable_type', DiscountCode::class)
        //     ->whereHas('referrable')

        //     ->orderBy('total_earnings', 'desc')
        //     ->first();



        $referralLink = ReferralEarning::query()
            ->where('user_id', $user->id)
            ->where('referrable_type', ReferralLink::class)
            ->whereHas('referrable')
            ->with('referrable.brand')
            ->orderByDesc('total_earnings')
            ->first();


// $discountCode = ReferralEarning::query()
//     ->where('referral_earnings.user_id', $user->id) 
//     ->where('referral_earnings.referrable_type', (new DiscountCode)->getMorphClass())
//     ->leftJoin('discount_codes', 'discount_codes.id', '=', 'referral_earnings.referrable_id')
//     ->whereNotNull('discount_codes.id')
//     ->orderByDesc('referral_earnings.total_earnings') 
//     ->select('referral_earnings.*')
//     ->first();


            $discountCode = ReferralEarning::query()
            ->where('user_id', $user->id)
            ->where('referrable_type', DiscountCode::class)
            ->whereHas('referrable')
            ->with('referrable.brand')
            ->orderByDesc('total_earnings')
            ->first();


        $total_earnings = ReferralEarning::where('user_id', $user->id)
            ->sum('total_earnings');
            
        $total_clients = ReferralEarning::where('user_id', $user->id)
            ->sum('total_clients');



        $now = Carbon::now();
        $startDate = $now->copy()->subDays(30);

        $intervals = [];


        for ($i = 0; $i < 6; $i++) {
            $from = $startDate->copy()->addDays($i * 5);
            $to = $from->copy()->addDays(5);

            $data = ReferralEarning::where('user_id', $user->id)
                ->where('referrable_type', DiscountCode::class)
                ->whereBetween('created_at', [$from, $to])
                ->select(
                    DB::raw('COALESCE(SUM(total_earnings), 0) as earnings'),
                    DB::raw('COALESCE(SUM(total_clients), 0) as clients')
                )
                ->first();

            $intervals[] = [
                'label' => $from->format('M d') . ' - ' . $to->format('M d'),
                'total_earnings' => $data->earnings,
                'total_clients' => $data->clients,
            ];
        
        
        }



        $data = [
            //  'referral_link' => $referralLink ? $referralLink->referalble ? new ReferralEarningResource($referralLink) : null : null,
            // 'descount_code' => $descountcode ? $descountcode->referalble ? new ReferralEarningResource($descountcode) : null : null,
            
            'referral_link' => $referralLink ?  new ReferralEarningResource($referralLink)  : null,
            'descount_code' => $discountCode ?  new ReferralEarningResource($discountCode)  : null,
            'chart_points'  => $intervals,
            'total_balance' => $customer->total_balance,
            'total_earning' => $total_earnings,
            'total_clients' => $total_clients

        ];

        return jsonResponse( true ,  200 , __('messages.created_successfully') ,$data);        
    }


    public function getVersion(VerifyVersionRequest $request)
    {

        $setting = null; 
        if($request->platform == 'android') {
            $setting = Setting::where('key' , 'android_app_version')->first();
        }else if($request->platform == 'ios') {
            $setting = Setting::where('key' , 'ios_app_version')->first();
        }else{
            return jsonResponse( false ,  422 ,__('messages.invalid_platform'));         
        }

        if(!$setting){
            return jsonResponse( false ,  500 ,__('messages.general_error_message'));         
        }

        if($setting->value == $request->version){
            return jsonResponse( true ,  200 ,__('messages.success') , ['up_to_date' => true]);         
        }

        return jsonResponse( true ,  200 ,__('messages.success') , ['up_to_date' => false , 'currenct_version' => $setting->value ]);                          
    }
}