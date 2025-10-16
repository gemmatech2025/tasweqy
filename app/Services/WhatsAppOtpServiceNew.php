<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsappSession;
  use Illuminate\Support\Str;

class WhatsAppOtpServiceNew
{
    protected $nodeServiceUrl;

    public function __construct()
    {
        $this->nodeServiceUrl = env('WHATSAPPWEB_OTP_SERVER_URL', 'https://otp.gemma-smart.com/');
    }

    /**
     * Format phone number by removing + prefix and any non-numeric characters
     * and adding @s.whatsapp.net suffix if not present
     *
     * @param string $phoneNumber
     * @return string
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove + prefix if present
        if (strpos($phoneNumber, '+') === 0) {
            $phoneNumber = ltrim($phoneNumber, '+');
        }

        // Remove all non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Add @s.whatsapp.net suffix if not already present
        if (!str_contains($phoneNumber, '@s.whatsapp.net')) {
            $phoneNumber = $phoneNumber . '@s.whatsapp.net';
        }

        return $phoneNumber;
    }

    private function getHeaders()
    {
        return [
            'x-passkey' => env('API_PASSKEY', 'Y29tcGxleC1zZWNyZXQta2V5LSVvW0xQNDNhU3Q='),
            'Content-Type' => 'application/json'
        ];
    }

    public function getSessionStatuses(array $clientIds)
    {
        try {
             $response = Http::withHeaders($this->getHeaders())->post( "{$this->nodeServiceUrl}client/get-status", [
                'clientIds' => $clientIds
            ]);
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error retrieving session statuses'];
        }
    }

    /**
     * Start a new WhatsApp session and get the QR code
     */
    public function startSession($clientId)
    {
        try {
            $payload = [
                'id' => $clientId,
                'isLegacy' => false,
                'callback' => env('WHATSAPPWEB_CALLBACK_URL', 'https://gemma-smart.gemmawhats.com/api/whatsapp-webhook'),
            ];

            $headers = $this->getHeaders();
            $response = Http::withHeaders($headers)->post( "{$this->nodeServiceUrl}sessions/add", $payload);

            if ($response->successful()) {
                return $response->json();
            } else {
                // Log the actual response for debugging
                $responseBody = $response->body();
                $statusCode = $response->status();

                return [
                    'success' => false,
                    'message' => 'Failed to start session',
                    'status_code' => $statusCode,
                    'response' => $responseBody
                ];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error starting session: ' . $e->getMessage()];
        }
    }

    /**
     * Check if session exists
     */
    public function findSession($sessionId)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())->get( "{$this->nodeServiceUrl}sessions/find/{$sessionId}");
            return $response->json();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error finding session: ' . $e->getMessage()];
        }
    }

    /**
     * Get session status with authentication state
     */
    public function getSessionStatus($sessionId)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())->get( "{$this->nodeServiceUrl}sessions/status/{$sessionId}");
            return $response->json();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error getting session status: ' . $e->getMessage()];
        }
    }

    /**
     * Get all sessions
     */


    /**
     * Get last QR code for session
     */
    public function getLastQr($sessionId)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())->get( "{$this->nodeServiceUrl}sessions/get-last-qr/{$sessionId}");
            return $response->json();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error getting last QR: ' . $e->getMessage()];
        }
    }

    /**
     * Refresh session
     */
    public function refreshSession($sessionId, $callbackUrl = null)
    {
        try {
            $payload = [];
            if ($callbackUrl) {
                $payload['callback'] = $callbackUrl;
            }

            $response = Http::withHeaders($this->getHeaders())->post( "{$this->nodeServiceUrl}sessions/refresh/{$sessionId}", $payload);
            return $response->json();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error refreshing session: ' . $e->getMessage()];
        }
    }

    /**
     * Delete a session
     */
    public function deleteSession($id)
    {
        try {
            $response = Http::withHeaders($this->getHeaders())->delete( "{$this->nodeServiceUrl}sessions/delete/{$id}");

            return $response->json();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error deleting session: ' . $e->getMessage()];
        }
    }

    /**
     * Send a unified message (text, media, or both) using the Node.js API
     *
     * This method intelligently routes to the appropriate endpoint based on media type
     * and uses proper multipart uploads for actual file sending.
     */
    public function sendMediaMessage($sessionId, $number, $message = null, $media = null, $replyToMessageId = null, $location = null)
    {
        try {
            $formattedNumber = $this->formatPhoneNumber($number);

            // If we have media, use the appropriate specific endpoint for actual file upload
            if ($media) {
                $fileMimeType = $media->getMimeType();

                // Route to specific endpoint based on media type for proper file upload
                if (strpos($fileMimeType, 'image/') === 0) {
                    return $this->sendImageMessage($sessionId, $number, $media, $message);
                } elseif (strpos($fileMimeType, 'video/') === 0) {
                    return $this->sendVideoMessage($sessionId, $number, $media, $message);
                } elseif (strpos($fileMimeType, 'audio/') === 0) {
                    return $this->sendAudioMessage($sessionId, $number, $media, true);
                } else {
                    // Document or other file type
                    return $this->sendDocumentMessage($sessionId, $number, $media, $message, $media->getClientOriginalName());
                }
            } else {
                // Text only - use text endpoint
                return $this->sendTextMessage($sessionId, $number, $message);
            }

        } catch (\Exception $e) {




            return [
                'success' => true,
                'message' => 'Message retry initiated immediately due to error: ' . $e->getMessage(),
                'retry_started' => true
            ];
        }
    }

    /**
     * Send text message using Node.js API
     */
    public function sendTextMessage($sessionId, $number, $message)
    {
        try {
            $url =  "{$this->nodeServiceUrl}messages/send-text/{$sessionId}";
            $headers = $this->getHeaders();

            $formattedNumber = $this->formatPhoneNumber($number);

            $payload = [
                'to' => $formattedNumber,
                'text' => $message,
            ];

            $response = Http::withHeaders($headers)->post($url, $payload);
            return $response->json();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error sending text message: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send image message using Node.js API with proper multipart upload
     */
    public function sendImageMessage($sessionId, $number, $image, $caption = null)
    {
        try {
            $url =  "{$this->nodeServiceUrl}messages/send-image/{$sessionId}";
            $formattedNumber = $this->formatPhoneNumber($number);

            // Create multipart form data exactly like Postman
            $multipartData = [
                [
                    'name' => 'to',
                    'contents' => $formattedNumber
                ],
                [
                    'name' => 'image',
                    'contents' => fopen($image->getPathname(), 'r'),
                    'filename' => $image->getClientOriginalName(),
                    'headers' => [
                        'Content-Type' => $image->getMimeType()
                    ]
                ]
            ];

            if ($caption) {
                $multipartData[] = [
                    'name' => 'caption',
                    'contents' => $caption
                ];
            }

            // Use Laravel HTTP client with multipart
            $response = Http::withHeaders([
                'x-passkey' => env('API_PASSKEY', 'Y29tcGxleC1zZWNyZXQta2V5LSVvW0xQNDNhU3Q=')
            ])->asMultipart()->post($url, $multipartData);

            return $response->json();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error sending image message: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send video message using Node.js API with proper multipart upload
     */
    public function sendVideoMessage($sessionId, $number, $video, $caption = null)
    {
        try {
            $url =  "{$this->nodeServiceUrl}messages/send-video/{$sessionId}";
            $formattedNumber = $this->formatPhoneNumber($number);

            // Create multipart form data for video
            $multipartData = [
                [
                    'name' => 'to',
                    'contents' => $formattedNumber
                ],
                [
                    'name' => 'video',
                    'contents' => fopen($video->getPathname(), 'r'),
                    'filename' => $video->getClientOriginalName(),
                    'headers' => [
                        'Content-Type' => $video->getMimeType()
                    ]
                ]
            ];

            if ($caption) {
                $multipartData[] = [
                    'name' => 'caption',
                    'contents' => $caption
                ];
            }

            $response = Http::withHeaders([
                'x-passkey' => env('API_PASSKEY', 'Y29tcGxleC1zZWNyZXQta2V5LSVvW0xQNDNhU3Q=')
            ])->asMultipart()->post($url, $multipartData);

            return $response->json();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error sending video message: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send audio message using Node.js API with proper multipart upload
     */
    public function sendAudioMessage($sessionId, $number, $audio, $ptt = true)
    {
        try {
            $url =  "{$this->nodeServiceUrl}messages/send-audio/{$sessionId}";
            $formattedNumber = $this->formatPhoneNumber($number);

            // Create multipart form data for audio
            $multipartData = [
                [
                    'name' => 'to',
                    'contents' => $formattedNumber
                ],
                [
                    'name' => 'audio',
                    'contents' => fopen($audio->getPathname(), 'r'),
                    'filename' => $audio->getClientOriginalName(),
                    'headers' => [
                        'Content-Type' => $audio->getMimeType()
                    ]
                ],
                [
                    'name' => 'ptt',
                    'contents' => $ptt ? 'true' : 'false'
                ]
            ];

            $response = Http::withHeaders([
                'x-passkey' => env('API_PASSKEY', 'Y29tcGxleC1zZWNyZXQta2V5LSVvW0xQNDNhU3Q=')
            ])->asMultipart()->post($url, $multipartData);

            return $response->json();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error sending audio message: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send document message using Node.js API with proper multipart upload
     */
    public function sendDocumentMessage($sessionId, $number, $document, $caption = null, $fileName = null)
    {
        try {
            $url =  "{$this->nodeServiceUrl}messages/send-document/{$sessionId}";
            $formattedNumber = $this->formatPhoneNumber($number);

            // Create multipart form data for document
            $multipartData = [
                [
                    'name' => 'to',
                    'contents' => $formattedNumber
                ],
                [
                    'name' => 'document',
                    'contents' => fopen($document->getPathname(), 'r'),
                    'filename' => $document->getClientOriginalName(),
                    'headers' => [
                        'Content-Type' => $document->getMimeType()
                    ]
                ]
            ];

            if ($caption) {
                $multipartData[] = [
                    'name' => 'caption',
                    'contents' => $caption
                ];
            }

            if ($fileName) {
                $multipartData[] = [
                    'name' => 'fileName',
                    'contents' => $fileName
                ];
            }

            $response = Http::withHeaders([
                'x-passkey' => env('API_PASSKEY', 'Y29tcGxleC1zZWNyZXQta2V5LSVvW0xQNDNhU3Q=')
            ])->asMultipart()->post($url, $multipartData);

            return $response->json();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error sending document message: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get messages from a chat using Node.js API
     */
    public function getMessages($sessionId, $chatId, $limit = 50)
    {
        try {
            $url =  "{$this->nodeServiceUrl}messages/messages/{$sessionId}";
            $headers = $this->getHeaders();

            $params = [
                'chatId' => $chatId,
                'limit' => $limit,
            ];

            $response = Http::withHeaders($headers)->get($url, $params);
            return $response->json();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting messages: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Download media from message using Node.js API
     */
    public function downloadMedia($sessionId, $messageId, $chatId)
    {
        try {
            $url =  "{$this->nodeServiceUrl}messages/download-media/{$sessionId}";
            $headers = $this->getHeaders();

            $params = [
                'messageId' => $messageId,
                'chatId' => $chatId,
            ];

            $response = Http::withHeaders($headers)->get($url, $params);
            return $response->json();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error downloading media: ' . $e->getMessage(),
            ];
        }
    }

    public function reactToMessage($clientId, $messageId, $emoji)
    {
        try {
            $url =  "{$this->nodeServiceUrl}client/react-to-message";
            $headers = $this->getHeaders();

            $data = [
                'clientId' => $clientId,
                'messageId' => $messageId,
                'emoji' => $emoji,
            ];

            $response = Http::withHeaders($headers)->post($url, $data);

            return $response->json();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send reaction to WhatsApp service',
            ];
        }
    }

    /**
     * Add ChatBot functionality
     */
    public function addChatBot($clientId, $prompt, $isActive)
    {
        try {
            $url =  "{$this->nodeServiceUrl}chat-boot/saveSession?id={$clientId}";

            $payload = [
                'prompt' => $prompt,
                'isActive' => $isActive,
            ];

             $response = Http::withHeaders($this->getHeaders())->post($url, $payload);

            if ($response->successful()) {

                return $response->json();
            } else {
                return ['success' => false, 'message' => 'Failed to add chatbot'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error adding chatbot'];
        }
    }

    public function toggleSessionStatus($clientId, $isActive)
    {
        try {
            $url =  "{$this->nodeServiceUrl}chat-boot/toggleSessionStatus?id={$clientId}";

            $payload = [
                'isActive' => (bool) $isActive,
            ];

             $response = Http::withHeaders($this->getHeaders())->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $payload);


            if ($response->successful()) {
                return $response->json();
            } else {
                return ['success' => false, 'message' => 'Failed to toggle session status'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error toggling session status'];
        }
    }

    public function getChats($id)
    {
        try {
             $response = Http::withHeaders($this->getHeaders())->get( "{$this->nodeServiceUrl}client/get-chats/$id");

            return $response->json();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error retrieving chats'];
        }
    }

    /**
     * Fetch messages for a specific chat
     */
    public function getMessagesWithMedia($jid, $accountId, $limit = 50)
    {
        try {
             $response = Http::withHeaders($this->getHeaders())->get( "{$this->nodeServiceUrl}client/get-messages-with-media/{$jid}/{$accountId}/{$limit}");

            return $response->json();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error retrieving messages'];
        }
    }

    public function getGroupMessages($groupId, $accountId, $limit = 50)
    {
        try {
             $response = Http::withHeaders($this->getHeaders())->get( "{$this->nodeServiceUrl}client/clients/{$accountId}/group/{$groupId}/messages/{$limit}");
            return $response->json();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error retrieving messages'];
        }
    }

    public function getSessionData($id)
    {
        try {
             $response = Http::withHeaders($this->getHeaders())->get( "{$this->nodeServiceUrl}client/get-client-data/$id");
            return $response->json();
        } catch (\Exception $e) {

            return ['success' => false, 'message' => 'Error retrieving session status'];
        }
    }

    public function getAllGroups($clientId)
    {
        try {
             $response = Http::withHeaders($this->getHeaders())->get( "{$this->nodeServiceUrl}client/get-all-groups/{$clientId}");

            return $response->json();
        } catch (\Exception $e) {

            return ['success' => false, 'message' => 'Error retrieving groups'];
        }
    }

    /**
     * Send message to group using Node.js API
     */
    public function sendMessageToGroup($sessionId, $groupId, $message = null, $media = null, $replyToMessageId = null, $location = null)
    {
        try {
            $url =  "{$this->nodeServiceUrl}messages/send/{$sessionId}";
            $headers = $this->getHeaders();

            $payload = [
                'to' => $groupId,
            ];

            if ($message) {
                $payload['text'] = $message;
            }

            if ($media) {
                $fileMimeType = $media->getMimeType();
                $filePath = $media->getPathname();
                $fileName = $media->getClientOriginalName();

                $payload['filePath'] = $filePath;
                if ($message) {
                    $payload['caption'] = $message;
                }
            }

            $response = Http::withHeaders($headers)->post($url, $payload);
            return $response->json();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error sending message to group: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send video to group using Node.js API
     */
    public function sendVideoToGroup($sessionId, $groupId, $message = null, $media = null)
    {
        try {
            $url =  "{$this->nodeServiceUrl}messages/send-video/{$sessionId}";
            $headers = $this->getHeaders();

            $multipartData = [
                ['name' => 'to', 'contents' => $groupId],
            ];

            if ($media) {
                $multipartData[] = ['name' => 'video', 'contents' => fopen($media->getPathname(), 'r'), 'filename' => $media->getClientOriginalName()];
                if ($message) {
                    $multipartData[] = ['name' => 'caption', 'contents' => $message];
                }
            } else {
                // Text only - use unified endpoint
                $url =  "{$this->nodeServiceUrl}messages/send/{$sessionId}";
                $payload = [
                    'to' => $groupId,
                    'text' => $message,
                ];
                $response = Http::withHeaders($headers)->post($url, $payload);
                return $response->json();
            }

            $response = Http::withHeaders($headers)->asMultipart()->post($url, $multipartData);
            return $response->json();

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error sending video to group: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send message to multiple contacts using Node.js API
     */
    public function sendMessageToMultiple($sessionId, array $numbers, $message = null, $media = null)
    {
        try {
            $results = [];
            $url =  "{$this->nodeServiceUrl}messages/send/{$sessionId}";
            $headers = $this->getHeaders();

            foreach ($numbers as $number) {
                $formattedNumber = $this->formatPhoneNumber($number);

                $payload = [
                    'to' => $formattedNumber,
                ];

                if ($message) {
                    $payload['text'] = $message;
                }

                if ($media) {
                    $fileMimeType = $media->getMimeType();
                    $filePath = $media->getPathname();
                    $fileName = $media->getClientOriginalName();

                    $payload['filePath'] = $filePath;
                    if ($message) {
                        $payload['caption'] = $message;
                    }
                }

                $response = Http::withHeaders($headers)->post($url, $payload);
                $results[] = [
                    'number' => $number,
                    'result' => $response->json()
                ];
            }

            return [
                'success' => true,
                'message' => 'Messages sent to multiple contacts',
                'results' => $results
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error sending message to multiple contacts: ' . $e->getMessage()];
        }
    }

    public function deleteChat($clientId, $chatId)
    {
        try {
            $url =  "{$this->nodeServiceUrl}client/delete-chat/{$clientId}/{$chatId}";

             $response = Http::withHeaders($this->getHeaders())->delete($url);

            return $response->json();
        } catch (\Exception $e) {

            return ['success' => false, 'message' => 'Error deleting chat'];
        }
    }

    public function updatePinnedStatus($clientId, $chatId)
    {
        try {
            $url =  "{$this->nodeServiceUrl}client/update-pinned-status/{$clientId}/{$chatId}";

             $response = Http::withHeaders($this->getHeaders())->get($url);

            return $response->json();
        } catch (\Exception $e) {

            return ['success' => false, 'message' => 'Error updating pinned status'];
        }
    }

    public function searchMessages($clientId, $query, $chatId = null, $page = 1, $limit = 20)
    {
        try {
            $url =  "{$this->nodeServiceUrl}client/clients/{$clientId}/messages/search";
            $params = [
                'clientId' => $clientId,
                'query' => $query,
                'chatId' => $chatId,
                'page' => $page,
                'limit' => $limit,
            ];

            $response = Http::withHeaders($this->getHeaders())->get($url, $params);
            return $response->json();
        } catch (\Exception $e) {

            return ['success' => false, 'message' => 'Error searching messages'];
        }
    }

    public function sendSeen($clientId, $chatId)
    {
        try {
            $url =  "{$this->nodeServiceUrl}client/clients/{$clientId}/chats/{$chatId}/seen";
            $headers = $this->getHeaders();


            $response = Http::withHeaders($headers)->post($url);
            return $response->json();
        } catch (\Exception $e) {

            return ['success' => false, 'message' => 'Error marking chat as seen'];
        }
    }

    public function deleteMessage($clientId, $chatId, $messageId, $everyone = false)
    {
        try {
            $queryString = http_build_query([
                'everyone' => $everyone ? 'true' : 'false'
            ]);

            $url =  "{$this->nodeServiceUrl}client/clients/{$clientId}/chats/{$chatId}/messages/{$messageId}?{$queryString}";

            $response = Http::withHeaders($this->getHeaders())->delete($url);

            return $response->json();
        } catch (\Exception $e) {

            return ['success' => false, 'message' => 'Error deleting message'];
        }
    }

    // Legacy methods for backward compatibility
public function sendWhatsappOtp(string $phoneNumber, string $body)
{
    try {
        if (Str::startsWith($phoneNumber, '+')) {
            $phoneNumber = ltrim($phoneNumber, '+');
        }

        $sessions = WhatsappSession::where('status', 'active')->get();

        if ($sessions->isEmpty()) {
            return false;
        }

        foreach ($sessions as $session) {
            $result = $this->sendMessageToWhatsapp($session->session_id, $phoneNumber, $body);
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

            return $sessions;

        } catch (\Exception $e) {
            Log::error('Exception while retrieving session statuses: ' . $e->getMessage());
            return false;
        }
    }
}
