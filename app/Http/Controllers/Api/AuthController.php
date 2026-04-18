<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function respondWithToken($token, $message)
    {
        return response()->json([
            'message' => $message,
            'access_token' => $token,
            'token_type' => 'bearer',
        ]);
    }

    // Регистрация нового пользователя
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);


        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);

        $token = auth()->login($user);

        return $this->respondWithToken($token, 'Registered successfully.');

    }

    // Логин пользователя и получение JWT.
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $token = auth()->attempt($data);

        if (!$token) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        return $this->respondWithToken($token, 'Login successfully.');

    }

    // Вернуть теккущего авторизованного рользователя
    public function me()
    {
        return response()->json(auth()->user());
    }

    // Выход пользователя
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    // Обновить токен
    public function refresh()
    {
        $token = auth()->refresh();
        return $this->respondWithToken($token, ' Token refreshed.');
    }

}
