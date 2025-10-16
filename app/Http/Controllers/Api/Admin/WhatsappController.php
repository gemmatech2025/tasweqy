<?php

namespace App\Http\Controllers\Api\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WhatsAppOtpServiceNew;
use App\Models\WhatsappSession;
use Illuminate\Support\Facades\Log;


class WhatsappController extends Controller
{

    protected $whatsAppOtpService;
    public function __construct(WhatsAppOtpServiceNew $whatsAppOtpService)
    {
        $this->whatsAppOtpService = $whatsAppOtpService;
    }


    public function createSession(Request $request)
{
    $session =WhatsappSession::where('session_name' ,$request->session_name )->first();
    if (!$session) {
        // Generate unique session ID
        $sessionId = $this->whatsAppOtpService->generateRandomText(10);

        // Ensure session ID is unique
        while (WhatsappSession::where('session_id', $sessionId)->exists()) {
            $sessionId = $this->whatsAppOtpService->generateRandomText(10);
        }

        $session = WhatsappSession::create([
            'session_name' => $request->session_name,
            'status' => 'qr',
            'session_id' => $sessionId,
        ]);
    }
    $result = $this->whatsAppOtpService->startSession($session->session_id);

    if (!$result) {
return jsonResponse(
        false,
        400,
        'an error happend please check log files.',
        );
}
    return jsonResponse(
        true,
        200,
        'success',
        $result
    );
}



public function handelWhatsappCallback(Request $request)
{

    $session = WhatsappSession::where('session_id' , $request->sessionId)->first();

    if(!$session){
    $result = $this->whatsAppOtpService->deleteSession($request->sessionId);

    if($result){
    }else{
             Log::info('session ' . $request->sessionId . 'deleted');
    }

    }

    if($request->status == 'active'){
        $session->status = 'active';
        $session->last_qr = null;

    }else if($request->status == 'logged_out'){

        if( $session){
        $session->status = 'logged_out';
        }
    }else if($request->status == 'restore_session_qr'){
         $result = $this->whatsAppOtpService->deleteSession($request->sessionId);

    if($result){
    }else{
             Log::info('session ' . $request->sessionId . 'deleted');
    }
        // if($session->qr){
        // $session->last_qr = $session->qr;}
    }else if($request->status == 'qr'){
        $session->status = 'qr';
       if($request->qr){
        $session->last_qr = $request->qr;
    }
    }
        $session->save();
            Log::info('------------------------------ whatsapp session update ------------------------------');
            Log::info(['Listener' => $request->all()]);
            Log::info('------------------------------ whatsapp session update ------------------------------');
}


public function deleteSession($id){
    $session =WhatsappSession::find($id);
    if(!$session){
    return jsonResponse(
        false,
        404,
        'not found',
        );
    }

    $result = $this->whatsAppOtpService->deleteSession($session->session_id);
    if($result){
        $session->delete();
    }else{
    return jsonResponse(
        false,
        404,
        'an error happend while deleting',
        );
    }

    return jsonResponse(
        true,
        200,
        'deleted successfully',
        );
}



    public function getSessions(Request $request)
    {
        $sessions = WhatsappSession::query();

        $name = $request->input('name', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        if ($name) {
            $sessions->where('session_name', 'like', '%' . $name . '%');
        }

        $sessions = $sessions->paginate($perPage, ['*'], 'page', $page);

        // Get status for each session from WhatsApp service and update local database
        $sessionsWithStatus = [];
        foreach ($sessions->items() as $session) {
            $sessionData = $session->toArray();

            // Get session status from WhatsApp service
            $statusResponse = $this->whatsAppOtpService->getSessionStatus($session->session_id);

            // Update local database with status from WhatsApp service
            if (isset($statusResponse['status'])) {
                $session->update([
                    'status' => $statusResponse['status']
                ]);
                $sessionData['status'] = $statusResponse['status'];
            }

            // Add status information to session data
            $sessionData['whatsapp_status'] = $statusResponse;
            $sessionData['is_connected'] = isset($statusResponse['status']) && $statusResponse['status'] === 'active';

            $sessionsWithStatus[] = $sessionData;
        }

        return jsonResponse(
            true,
            200,
            __('messages.fetch_successful'),
            $sessionsWithStatus,
            [
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'total' => $sessions->total(),
                'per_page' => $sessions->perPage(),
                'request_url' => $request->fullUrl(),
            ]
        );
    }

    public function getSessionDetails($id)
    {
        $session = WhatsappSession::find($id);

        if(!$session){
            return jsonResponse(false,404,'not found',);
        }

        return jsonResponse(true, 200, __('messages.fetch_successful'), $session);
    }




}
