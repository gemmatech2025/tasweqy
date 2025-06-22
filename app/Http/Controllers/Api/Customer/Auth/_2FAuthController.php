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



use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;
use App\Http\Requests\Customer\Auth\Verify2FACodeRequest;


class _2FAuthController extends Controller
{


    public function enable2FA() {

        try{
        $user = Auth::user();
        $code = app(Google2FA::class)->generateSecretKey();
        if (!$user->two_factor_secret) {
            $user->forceFill([
                'two_factor_secret' => encrypt($code),
                'two_factor_recovery_codes' => encrypt(json_encode(collect(range(1, 8))->map(function () {
                    return Str::random(10) . '-' . Str::random(10);
                })->all())),
            ])->save();
        }
        return jsonResponse( true ,  200 ,__('messages.2FA_enabled') );    

    }catch (\Exception $e) {
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


public function getQrCode()
{
    try {
        $user = Auth::user();
        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);

        $otpAuthUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email, 
            $secret
        );

                return jsonResponse( true ,  200 ,__('messages.2FA_enabled') ,[
                'secret' => $secret,
                'otpauth_url' => $otpAuthUrl,
                'issuer' => config('app.name'),
                'email' => $user->email,
            ]);    


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





public function verify2FACode(Verify2FACodeRequest $request)
{
    try {
        $user = Auth::user();

        $google2fa =  new Google2FA();

        $valid = $google2fa->verifyKey(decrypt($user->two_factor_secret), $request->code);

        if (!$valid) {
            return jsonResponse( false ,  403 ,__('messages.invalid_code'));    
        }
        $user->two_factor_confirmed_at = now();
        $user->save();
        return jsonResponse( true ,  200 ,__('messages.2FA_verified_successfully'));    
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




public function disable2FA()
{
    try {
        $user = Auth::user();

        $user->forceFill([
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null, 

        ])->save();

        return jsonResponse( true ,  200 ,__('messages.2FA_disabled'));    
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




public function regenerateRecoveryCodes()
{
    try {
        $user = Auth::user();

        $codes = collect(range(1, 8))->map(function () {
        return Str::random(10) . '-' . Str::random(10);
        })->all();

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ])->save();

        return jsonResponse( true ,  200 ,__('messages.Recovery_codes_regenerated') , ['recovery_codes' => $codes]);    
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



public function useRecoveryCode(Verify2FACodeRequest $request)
{
    try {

        $user = Auth::user();
        if (is_null($user->two_factor_recovery_codes)) {
            return jsonResponse(false, 403, __('messages.no_recovery_codes'));
        }
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        if (!in_array($request->code, $recoveryCodes)) {
            return jsonResponse(false, 403, __('messages.invalid_recovery_code'));
        }

        $updatedCodes = collect($recoveryCodes)
            ->reject(fn($code) => $code === $request->code)
            ->values()
            ->all();

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($updatedCodes)),
        ])->save();

        return jsonResponse(true, 200, __('messages.recovery_code_accepted'));
    } catch (\Exception $e) {
        return jsonResponse(false, 500, __('messages.general_message'), null, null, [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);
    }
}



}