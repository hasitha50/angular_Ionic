<?php

namespace App\Http\Controllers\AUth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);
    
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => __('The provided credentials do not match our records.'),
            ]);
        }
    
        $user = Auth::user();
    
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Invalid credentials.'],
            ]);
        }
    
        $token = $user->createToken('API Token')->accessToken;
    
        // Set FCM token if present
        if ($request->filled('fcm_token')) {
            $this->setToken($user, $request->input('fcm_token'));
        }
    
        return response()->json([
            'message' => ('Login successful'),
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    public function userCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'nullable|in:Business,Individual,AppOwner',
            'phone' => 'nullable|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'address' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'pincode' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'role' => 'buyer', // default role
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'pincode' => $request->pincode,
            'image' => $request->image,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }


    public function updateUser(Request $request)
    {
        $user = User::find(auth::user()->id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'pincode' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update([
            'phone' => $request->phone,
            'address' => $request->address,
            'pincode' => $request->pincode,
            'image' => $request->image ?? $user->image,
        ]);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ], 200);
    }


    public function logout(Request $request)
    {
        // Revoke the token  
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Logged out'], 200);
    }

    public function authUser(Request $request)
    {
        $user = Auth::user();
        return response()->json($user);
    }

    public function setToken($user, $fcm_token)
    {
        $user->update([
            'fcm_token' => $fcm_token
        ]);
    }
}
