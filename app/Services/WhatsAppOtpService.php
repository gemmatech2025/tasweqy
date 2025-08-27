<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsappSession;
class WhatsAppOtpService
{
    protected $nodeServiceUrl;

    public function __construct()
    {
        $this->nodeServiceUrl = env('WHATSAPPWEB_OTP_SERVER_URL', 'https://otp.gemma-smart.com/');
    }

    public function sendWhatsappOtp(string $phoneNumber, string $body)
    {
    try {


        $sessions = WhatsappSession::where('status', 'active')->get();

        if($sessions->isEmpty()){
            return false;
        }

        foreach ($sessions as $session) {

            $result = $this->sendMessageToWhatsapp($session->session_id,$phoneNumber, $body);
            if ($result) {
                return true;
            }
        }


        return false;

    } catch (\Exception $e) {
        Log::error('Exception while retrieving session statuses: ' . $e->getMessage());
        return false;
    }
}

public function sendMessageToWhatsapp($sessionId ,$phoneNumber, $body)
{
    try {


        $response = Http::post("{$this->nodeServiceUrl}chats/send?id={$sessionId}", [
            "receiver" => $phoneNumber,
            "message" => [
                "text" => $body
            ]
        ]);

        if (!$response->successful()) {
            Log::error("Failed to send message using session {$sessionId}. HTTP Status: " . $response->status());
            return false;
        }

        return true;

    } catch (\Exception $e) {
        Log::error("Exception while sending message using session {$sessionId}: " . $e->getMessage());
        return false;
    }
}




    public function generateRandomText($length = 10)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomText = substr(str_shuffle(str_repeat($characters, ceil($length / strlen($characters)))), 0, $length);
        return $randomText;
    }



    public function startSession($sessionName)
    {
    try {



        $id = $this->generateRandomText(6);
        $response = Http::post("{$this->nodeServiceUrl}sessions/add", [
            'callback' => env('WHATSAPPWEB_CALLBACK_URL', 'https://back-tasweqy.gemmawhats.com/api/handel-callback'),
            "id"           => $id ,
            "isLegacy"     => 1
        ]);
        if (!$response->successful()) {
            return false;
        }

        $data = $response->json();
        $message = null;
        $qr = null ;


        $session = WhatsappSession::create([
            'session_name'   => $sessionName,
            'status'         => 'qr',
            'session_id'     => $id,
        ]);

        if(isset($data['message'])){
            $message = $data['message'];
        }else{
            $message = $data['message'];
        }


        if (!empty($data['data']['qr'])) {
        $qr = $data['data']['qr'];
        $session->last_qr = $qr;
        $session->save();
        }


        return [
            'session_details' => $session ,
            'qr'              => $qr,
            'message'         => $message,
        ];

    } catch (\Exception $e) {
        Log::error('Exception while retrieving session statuses: ' . $e->getMessage());
        return false;
    }
}




   public function deleteSession($id)
    {
    try {

        $response = Http::delete("{$this->nodeServiceUrl}sessions/delete/" .$id );
        if (!$response->successful()) {
            return false;
        }
        return true;

    } catch (\Exception $e) {
        Log::error('Exception while retrieving session statuses: ' . $e->getMessage());
        return false;
    }
}



  public function getSessions()
    {
    try {

        $response = Http::get("{$this->nodeServiceUrl}sessions/sessions");

        if (!$response->successful()) {
            Log::error('Failed to retrieve sessions. HTTP Status: ' . $response->status());
            return false;
        }

        $json = $response->json();

        if (!isset($json['message'])) {
            Log::error('Missing "message" key in session response');
            return false;
        }

        $sessions = $json['message'];

        if (empty($sessions)) {
            Log::error('No active sessions found');
            return false;
        }


        return $sessions ;


    } catch (\Exception $e) {
        Log::error('Exception while retrieving session statuses: ' . $e->getMessage());
        return false;
    }
}

}
