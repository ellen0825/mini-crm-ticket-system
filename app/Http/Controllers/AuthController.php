<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['sometimes', 'in:admin,operator'],
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => $validated['password'],
            'role'      => $validated['role'] ?? 'operator',
            'api_token' => Str::random(80),
        ]);

        return response()->json([
            'user'  => $user->only('id', 'name', 'email', 'role'),
            'token' => $user->api_token,
        ], Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        $user->forceFill(['api_token' => Str::random(80)])->save();

        return response()->json([
            'user'  => $user->only('id', 'name', 'email', 'role'),
            'token' => $user->api_token,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->attributes->get('api_user');
        $user?->forceFill(['api_token' => null])->save();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        $user = $request->attributes->get('api_user');

        return response()->json($user->only('id', 'name', 'email', 'role'));
    }
}
