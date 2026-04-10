<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (session('admin_user_id')) {
            return redirect()->route('admin.tickets.index');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->hasRole('admin')) {
            return back()->withErrors(['email' => 'Invalid credentials or insufficient permissions.'])->withInput();
        }

        $request->session()->regenerate();
        session(['admin_user_id' => $user->id]);

        return redirect()->route('admin.tickets.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_user_id');
        $request->session()->regenerate();

        return redirect()->route('admin.login');
    }
}
