<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthTokenController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        $token = $user->createToken('api', ['products:read','stocks:read']);

        return response()->json([
            'token'     => $token->plainTextToken,
            'tokenType' => 'Bearer',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
