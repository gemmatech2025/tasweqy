<?php

namespace App\Http\Controllers\Api\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\WhatsappSession;
use Illuminate\Support\Facades\Log;


class WhatsappController extends Controller
{
    protected $whatsAppOtpService;
    public function __construct(WhatsAppOtpService $whatsAppOtpService)
    {
        $this->whatsAppOtpService = $whatsAppOtpService;
    }

public function createSession(Request $request)
{
    $session =WhatsappSession::where('session_name' ,$request->session_name )->first();
    if($session){
         return jsonResponse(
        false,
        400,
        'session name already exists',
        );
    }

     if(!$request->session_name){
         return jsonResponse(
        false,
        422,
        'session name is required',
        );
    }

    $result = $this->whatsAppOtpService->startSession($request->session_name);

    if(!$result){
    return jsonResponse(
            false,
            400,
            'an error happend please check log files',
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
    $page =$request->input('page', 1);
    $perPage = $request->input('per_page', 20);

    if ($name) {
        $sessions->where('session_name', 'like', '%' . $name . '%');
    }

        $data = $sessions->paginate($perPage, ['*'], 'page', $page);

         return jsonResponse(
                    true,
                    200,
                    __('messages.fetch_successful'),
                         $data,

    );
}

public function getSessionDetails($id)
{
    $session = WhatsappSession::find($id);

        if(!$session){
    return jsonResponse(
        false,
        404,
        'not found',
        );
    }

         return jsonResponse(
                    true,
                    200,
                    __('messages.fetch_successful'),
                        $session,

    );
}



}
