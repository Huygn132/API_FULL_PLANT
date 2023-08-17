<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $userName = $request->input('user_name');
        $password = $request->input('password');

        $user = User::where('user_name', $userName)->first();

        if (!$user) {
            return response()->json(['error' => 'err_mes01', 'message' => "UserID doesn't exist!"], 400);
        }

        if ($user->del_flg == 1) {
            return response()->json(['error' => 'err_mes02', 'message' => 'UserID is not available!'], 400);
        }

        if ($password !== $user->password) {
            return response()->json(['error' => 'err_mes03', 'message' => 'Password is incorrect!'], 400);
        }

        return response()->json(['message' => 'Login successful!', 'user' => $user], 200);
    }
}
