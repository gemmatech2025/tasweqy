<?php

namespace App\Http\Controllers\Api\Customer\Profile;

use App\Http\Controllers\Controller;


use App\Models\User;
use App\Models\Otp;
use App\Models\Customer;




use Illuminate\Support\Facades\DB;


use App\Http\Resources\Customer\CustomerResource;


use Illuminate\Http\Request;
use App\Http\Requests\Customer\Profile\UpdateProfileRequest;




class CustomerController extends Controller
{


    public function updateProfile(UpdateProfileRequest $request)
    {
        DB::beginTransaction();
    
        try {
            
            $user = Auth::user();

            $customer = Customer::where('user_id' , $user->id)->first();
            $country = Country::find($request->country_id);

            if($customer){

                if($request->phone !=  $customer->phone ){
                    $user->phone_verified_at = null;
                }
                $customer->country_id = $request->country_id;
                $customer->birthdate = $request->birthdate;
                $customer->gender = $request->gender;
                $user->phone = $request->phone;
                $user->code = $country->code;
                $customer->save();
            }else{

                $customer = Customer::create([
                    'country_id'     => $request->country_id,
                    'birthdate'      => $request->birthdate,
                    'gender'         => $request->gender,
                
                ]);
                $user->phone = $request->phone;
                $user->code = $country->code;
            }

            if(!$user->phone_verified_at){
                $result = $this->sendPhoneOtp($country->code .$request->phone , $customer->id);

                if($result){
                    return jsonResponse( true ,  201 ,__('messages.data_updated_successfully_please_send_otp_on_your_whatsapp') ,
                      ['whatsapp_otp' =>true , 'customer' => new CustomerResource($customer)] );    
                }

                return jsonResponse( true ,  201 ,__('messages.data_updated_successfully_but_otp_not_sent_make_sure_phone_number_correct') ,
                      ['whatsapp_otp' =>false , 'customer' => new CustomerResource($customer)] );    


            }

            
            return jsonResponse( true ,  201 ,__('messages.data_updated_successfully') ,
                      ['whatsapp_otp' =>false , 'customer' => new CustomerResource($customer)] );                            
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
        $customer = Customer::where('user_id' , $user->id)->first();
        if(!$customer){
            return jsonResponse( true ,  404 ,__('messages.customer_not_found_complete_profile') , null,null,[] );        
        }                       
        return jsonResponse( true ,  200 ,__('messages.sucess') , new CustomerResource($customer) );        
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
            'user_id'=> $user->id,
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