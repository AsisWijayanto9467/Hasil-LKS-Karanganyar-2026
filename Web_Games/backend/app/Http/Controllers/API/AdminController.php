<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Administrator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AdminController extends Controller
{
    public function getAdmin(Request $request)
    {
        try {
            // Ambil token yang sedang dipakai
            $token = $request->bearerToken();
            $accessToken = PersonalAccessToken::findToken($token);

            // Pastikan token ADMIN
            if (!$accessToken || $accessToken->name !== 'admin_token') {
                return response()->json([
                    'status'  => 'forbidden',
                    'message' => 'You are not the administrator'
                ], 403);
            }

            $admins = Administrator::select(
                'username',
                'last_login_at',
                'created_at',
                'updated_at'
            )->get();

            return response()->json([
                'totalElements' => $admins->count(),
                'content'       => $admins
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to get admin data',
                'error'   => config('app.debug') ? $th->getMessage() : null
            ], 500);
        }
    }

    public function storeUser(Request $request) {
        try {
            // 🔐 Ambil token & pastikan ADMIN
            $token = $request->bearerToken();
            $accessToken = PersonalAccessToken::findToken($token);

            if (!$accessToken || $accessToken->name !== 'admin_token') {
                return response()->json([
                    'status'  => 'forbidden',
                    'message' => 'You are not the administrator'
                ], 403);
            }

            $request->validate([
                'username' => 'required|string|min:4|max:60|unique:users,username',
                'password' => 'required|string|min:5|max:20',
            ]);

            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'status'   => 'success',
                'username' => $user->username
            ], 201);

        } catch (ValidationException $e) {
            if (isset($e->errors()['username'])) {
                return response()->json([
                    'status'  => 'invalid',
                    'message' => 'Username already exists'
                ], 400);
            }

            throw $e;

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'gagal membuat user',
                'error'   => config('app.debug') ? $th->getMessage() : null
            ], 500);
        }
    }

    public function getUser(Request $request) {
        try {
            // 🔐 Ambil token & pastikan ADMIN
            $token = $request->bearerToken();
            $accessToken = PersonalAccessToken::findToken($token);

            if (!$accessToken || $accessToken->name !== 'admin_token') {
                return response()->json([
                    'status'  => 'forbidden',
                    'message' => 'You are not the administrator'
                ], 403);
            }

            $users = User::select(
                'username',
                'last_login_at',
                'created_at',
                'updated_at'
            )->get();

            return response()->json([
                'totalElements' => $users->count(),
                'content' => $users
            ], 200);

        } catch (ValidationException $e) {

            // ❌ Username sudah ada
            if (isset($e->errors()['username'])) {
                return response()->json([
                    'status'  => 'invalid',
                    'message' => 'Username already exists'
                ], 400);
            }

            throw $e;

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'gagal membuat user',
                'error'   => config('app.debug') ? $th->getMessage() : null
            ], 500);
        }
    }

    public function updateUser(Request $request, $id){
        try {
            // 🔐 Pastikan ADMIN
            $token = $request->bearerToken();
            $accessToken = PersonalAccessToken::findToken($token);

            if (!$accessToken || $accessToken->name !== 'admin_token') {
                return response()->json([
                    'status'  => 'forbidden',
                    'message' => 'You are not the administrator'
                ], 403);
            }
            $user = User::findOrFail($id);

            // ✅ Validasi input
            $request->validate([
                'username' => "required|string|min:4|max:60|unique:users,username,$id",
                'password' => 'required|string|min:5|max:20',
            ]);

            $user->update([
                'username' => $request->username,
                'password' => $request->password,
            ]);

            return response()->json([
                'status'   => 'success',
                'username' => $user->username
            ], 201);

        } catch (ValidationException $e) {

            if (isset($e->errors()['username'])) {
                return response()->json([
                    'status'  => 'invalid',
                    'message' => 'Username already exists'
                ], 400);
            }
            throw $e;
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'failed to update user',
                'error'   => config('app.debug') ? $th->getMessage() : null
            ], 500);
        }
    }

    public function deleteUser(Request $request, $id){
        try {
            // 🔐 Pastikan ADMIN
            $token = $request->bearerToken();
            $accessToken = PersonalAccessToken::findToken($token);

            if (!$accessToken || $accessToken->name !== 'admin_token') {
                return response()->json([
                    'status'  => 'forbidden',
                    'message' => 'You are not the administrator'
                ], 403);
            }

            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'status'  => 'not-found',
                    'message' => 'User Not found'
                ], 403);
            }

            $user->delete();

            return response()->noContent();

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'failed to delete user',
                'error'   => config('app.debug') ? $th->getMessage() : null
            ], 500);
        }
    }
}
