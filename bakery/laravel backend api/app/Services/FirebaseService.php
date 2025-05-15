<?php


namespace App\Services;

use App\Models\NotificationModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FirebaseService
{
    protected $messaging;
    public function __construct()
    {
        $serviceAccountPath = storage_path('angular-ionic-test-firebase-adminsdk-fbsvc-36de2aee05.json');
        $factory = (new Factory)->withServiceAccount($serviceAccountPath);
        $this->messaging = $factory->createMessaging();
    }


    public function sendNotification($token,$title, $body, $data = [])
    {
        $AuthUser = Auth::user();
        
       
        if (empty($token)) {
            return response()->json(['error' => 'Device token is missing'], 422);
        }

        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(['title' => $title, 'body' => $body])
                ->withData($data);

            $this->messaging->send($message);

            $toUser = User::where('fcm_token', $token)->first();
            log::info( $toUser);
            if (!$toUser) {
                return response()->json(['error' => 'Recipient user not found'], 422);
            }
            
            // Save to database
            NotificationModel::create([
                'user_id' => $AuthUser->id,
                'to_user_id' => $toUser->id,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);

            return response()->json(['message' => 'Notification sent and saved.']);

        } catch (\Throwable $e) {
            Log::error('FCM Notification Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to send notification',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
