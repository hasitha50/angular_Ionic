<?php

namespace App\Http\Controllers;

use App\Models\NotificationModel;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected $FirebaseService;
    public function __construct(FirebaseService $FirebaseService)
    {
        $this->FirebaseService = $FirebaseService;
    }
    public function setToken(Request $request)
    {
        $token = $request->input('fcm_token');
        $request->user()->update([
            'fcm_token' => $token
        ]);
        return response()->json([
            'message' => 'Successfully Updated FCM Token'
        ]);
    }



    public function sendPushNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
 
        $deviceToken = $request->token;
        $title = $request->title;
        $body = $request->body;
        $customData = $request->data ?? [];
    
        $response = $this->FirebaseService->sendNotification($deviceToken, $title, $body, $customData);
    
        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully.',
            'response' => $response,
        ]);
    }
    


    public function getUserNotifications()
    {
        try {
            $user = Auth::user();

            $notifications = NotificationModel::where('to_user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Notifications retrieved successfully',
                'notifications' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve notifications',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteNotification($id)
    {
        try {
            $user = Auth::user();

            $notification = NotificationModel::where('id', $id)
                ->where('to_user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'error' => 'Notification not found or unauthorized access',
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete notification',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
