<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserStatusController extends Controller
{
    public function check(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'authenticated' => false,
                'active' => false,
                'reason' => 'not_authenticated'
            ], 401);
        }
        
        $user = Auth::user();
        
        if (!$user->Activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return response()->json([
                'authenticated' => false,
                'active' => false,
                'reason' => 'user_inactive'
            ], 403);
        }
        
        return response()->json([
            'authenticated' => true,
            'active' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name
            ]
        ]);
    }
}