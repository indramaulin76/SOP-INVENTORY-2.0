<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     * 
     * SECURITY: Uses LoginRequest with built-in rate limiting (5 attempts max)
     * to prevent brute force attacks.
     */
    public function store(LoginRequest $request)
    {
        // Check if user exists and is active BEFORE authentication
        $user = \App\Models\User::where('email', $request->email)->first();
        
        if ($user && !$user->active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Admin.',
            ]);
        }

        // Authenticate with rate limiting protection
        $request->authenticate();

        $request->session()->regenerate();

        // Update last login timestamp
        Auth::user()->updateLastLogin();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session (Logout).
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
