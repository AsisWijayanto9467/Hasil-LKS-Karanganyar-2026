<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    private function convertMessage($msg)
    {
        if (str_contains($msg, 'required')) {
            return 'required';
        }

        if (str_contains($msg, 'at least')) {
            preg_match('/\d+/', $msg, $m);
            return 'must be at least ' . ($m[0] ?? '') . ' characters long';
        }

        if (str_contains($msg, 'may not be greater')) {
            preg_match('/\d+/', $msg, $m);
            return 'must be at most ' . ($m[0] ?? '') . ' characters long';
        }

        if (str_contains($msg, 'has already been taken')) {
            return 'already exists';
        }

        return $msg;
    }

    public function signup(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                "username" => "required|string|min:4|max:60|unique:users,username",
                "password" => "required|min:5|max:20"
            ]);

            if ($validator->fails()) {

                $violations = [];

                foreach ($validator->errors()->messages() as $field => $messages) {
                    $violations[$field] = [
                        "message" => $this->convertMessage($messages[0])
                    ];
                }

                return response()->json([
                    "status" => "invalid",
                    "message" => "Request body is not valid.",
                    "violations" => $violations
                ], 400);
            }


            User::create([
                "username" => $request->username,
                "password" => Hash::make($request->password)
            ]);

            $user = User::where("username", $request->username)->firstOrFail();

            $token = $user->createToken("auth_token")->plainTextToken;

            return response()->json([
                "status" => "success",
                "token" => $token
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => "signup gagal",
                "debug" => config("app.debug") ? $th->getMessage() : null
            ], 500);
        }
    }

    public function signin(Request $request) {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $user = User::where('username', $request->username)->first();

            if ($user && Hash::check($request->password, $user->password)) {

                $user->update([
                    'last_login_at' => now()
                ]);

                $token = $user->createToken('user_token')->plainTextToken;

                return response()->json([
                    'status' => 'success',
                    'token'  => $token
                ], 200);
            }

            $admin = Administrator::where('username', $request->username)->first();

            if ($admin && Hash::check($request->password, $admin->password)) {

                $admin->update([
                    'last_login_at' => now()
                ]);

                $token = $admin->createToken('admin_token')->plainTextToken;

                return response()->json([
                    'status' => 'success',
                    'token'  => $token
                ], 200);
            }

            return response()->json([
                'status'  => 'invalid',
                'message' => 'Wrong username or password'
            ], 401);
        } catch (\Throwable $th) {
           return response()->json([
                "status" => "error",
                "message" => "signin gagal",
                "error" => config("app.debug") ? $th->getMessage() : null
            ], 500);
        }
    }

    public function signout(Request $request) {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'status' => 'invalid',
                    'message' => 'Token not provided'
                ], 401);
            }

            $accessToken = PersonalAccessToken::findToken($token);

            if (!$accessToken) {
                return response()->json([
                    'status' => 'invalid',
                    'message' => 'Invalid token'
                ], 401);
            }

            $accessToken->delete();

            return response()->json([
                "status" => "success"
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => "signout gagal",
                "debug" => config("app.debug") ? $th->getMessage() : null
            ], 500);
        }
    }
}
