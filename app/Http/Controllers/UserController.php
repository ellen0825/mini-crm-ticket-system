<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('roles')
            ->select('id', 'name', 'email', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $u) => $this->formatUser($u));

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($this->formatUser($user->load('roles')));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return response()->json($this->formatUser($user->load('roles')));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->attributes->get('api_user')->id === $user->id) {
            return response()->json(
                ['message' => 'You cannot delete your own account.'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $user->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'roles'      => $user->getRoleNames(),
            'created_at' => $user->created_at,
        ];
    }
}
