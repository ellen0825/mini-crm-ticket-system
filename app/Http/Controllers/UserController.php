<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::select('id', 'name', 'email', 'role', 'created_at')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function show(User $user)
    {
        return response()->json(
            $user->only('id', 'name', 'email', 'role', 'created_at')
        );
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'  => ['sometimes', 'in:admin,operator'],
        ]);

        $user->update($validated);

        return response()->json($user->only('id', 'name', 'email', 'role'));
    }

    public function destroy(Request $request, User $user)
    {
        $currentUser = $request->attributes->get('api_user');

        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
