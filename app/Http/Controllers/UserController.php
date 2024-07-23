<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function me()
    {
        $user = Auth::user();
        if (!$user) {
            return Response::error('User not found', 404);
        }

        return Response::success($user);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return Response::error('User not found', 404);
        }

        if (!Hash::check($request->input('password'), $user->password)) {
            return Response::error('Password is incorrect', 401);
        }

        $payload = [
            'iat' => intval(microtime(true)),
            'exp' => intval(microtime(true) + (60 * 60 * 24 * 7)),
            'uid' => $user->id,
        ];

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        $res = [
            'token_type' => 'Bearer',
            'expires_in' => 60 * 60 * 24 * 7,
            'access_token' => $token
        ];
        return Response::success($res);
    }

    public function logout()
    {
        $user = Auth::user();
        if (!$user) {
            return Response::error('User not found', 404);
        }

        return Response::success('Logout success');
    }
}
