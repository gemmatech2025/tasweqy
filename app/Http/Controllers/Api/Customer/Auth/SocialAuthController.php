<?php

namespace App\Http\Controllers\Api\Customer\Auth;

use App\Http\Controllers\Controller;


use App\Models\User;


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Http\Requests\Customer\Auth\LoginRequest;

use Laravel\Socialite\Facades\Socialite;
use Google_Client;


class SocialAuthController extends Controller
{

    // protected $whatsAppWebService = null;
    // protected $uploadFilesService = null;

    // public function __construct()
    // {
    //     $this->whatsAppWebService = new WhatsAppOtpService();
    //     $this->uploadFilesService = new UploadFilesService();

    // }

  public function googleLogin(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:google',
            'access_token' => 'required|string',
        ]);

        try {
            $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);

            $payload = $client->verifyIdToken($request->access_token);

            if (!$payload) {
                return response()->json(['message' => 'Invalid ID token'], 401);
            }

            $email = $payload['email'] ?? null;

            if (!$email) {
                return response()->json(['message' => 'No email found in token'], 400);
            }

            $customer = Customer::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $payload['name'] ?? '',
                    'google_id' => $payload['sub'],
                    'avatar' => $payload['picture'] ?? '',
                    'status' => 1,
                    'blocked' => false,
                ]
            );

            $token = $customer->createToken('customer_token', ['*'])->plainTextToken;

            return response()->json([
                'token' => $token,
                'customer' => $customer,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token validation failed',
                'error' => $e->getMessage(),
            ], 401);
        }
    }





    public function loginWithFacebook(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            $facebookUser = Socialite::driver('facebook')
                ->stateless()
                ->userFromToken($request->access_token);

            $user = User::firstOrCreate(
                ['email' => $facebookUser->getEmail()],
                [
                    'name' => $facebookUser->getName(),
                    'facebook_id' => $facebookUser->getId(),
                    'password' => bcrypt(str()->random(16)), // or null if you want
                ]
            );

            // Issue token (if using Sanctum or Passport)
            $token = $user->createToken('facebook-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'user' => $user,
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Facebook login failed',
                'error' => $e->getMessage(),
            ], 401);
        }
    }



public function getGoogleAuthUrl()
{
    $redirectUrl = Socialite::driver('google')
        ->stateless()
        ->redirect()
        ->getTargetUrl();

    return response()->json([
        'url' => $redirectUrl
    ]);
}

public function googleCallback(Request $request)
{
    try {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $accessToken = $googleUser->token;

        return response()->json([
            'access_token' => $accessToken,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to get token',
            'message' => $e->getMessage()
        ], 500);
    }
}


public function socialLogin(Request $request)
{
    $request->validate([
        'provider' => 'required|in:google,facebook',
        'access_token' => 'required|string',
    ]);

    try {
        $provider = $request->provider;
        $email = null;
        $name = null;
        $avatar = null;
        $providerId = null;

        if ($provider === 'google') {
             $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->access_token);

    $email = $googleUser->getEmail();
    $name = $googleUser->getName();
    $avatar = $googleUser->getAvatar();
    $providerId = $googleUser->getId();

    if (!$email) {
        return jsonResponse(false, 400, __('messages.email_not_found_in_google_profile'));
    }
            // $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            // $payload = $client->verifyIdToken($request->access_token);

            // if (!$payload) {
            //     return jsonResponse( false ,  401 ,__('messages.invalid_google_token') );    
            // }

            // $email = $payload['email'] ?? null;
            // $name = $payload['name'] ?? null;
            // $avatar = $payload['picture'] ?? null;
            // $providerId = $payload['sub'] ?? null;

            // if (!$email) {
            //     return jsonResponse( false ,  400 ,__('messages.email_not_found_in_google_token') );    
            // }

        } elseif ($provider === 'facebook') {
            $facebookUser = Socialite::driver('facebook')
                ->stateless()
                ->userFromToken($request->access_token);

            $email = $facebookUser->getEmail();
            $name = $facebookUser->getName();
            $avatar = $facebookUser->getAvatar();
            $providerId = $facebookUser->getId();

            if (!$email) {
                return jsonResponse( false ,  400 ,__('messages.email_not_found_in_facebook_profile') );    
            }
        }else{
                return jsonResponse( false ,  400 ,__('messages.invalid_type') );    
        }
        $customer = User::updateOrCreate(
            ['email' => $email],
            [
                'email_verified_at' => now(),
                'name'              => $name ?? '',
                $provider . '_id'   => $providerId,
                'image'             => $avatar ?? '',
                'password'          => Hash::make($providerId . 'password'), 
                'role'              => 'customer',
            ]
        );

        // Generate token
        $token = $customer->createToken('customer_token', ['*'])->plainTextToken;


        return jsonResponse( true ,  200 ,__('messages.authanticated_successfully') ,
        ['token' => $token,
        'customer' => $customer] );    


    } catch (\Exception $e) {


        return jsonResponse( false ,  401 ,__('messages.authentication_failed'),
            null ,
            null ,
            $e->getMessage(),
     );    

    }
}





}