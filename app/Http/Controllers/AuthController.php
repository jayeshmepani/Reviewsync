<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class AuthController
{
    protected function redirectBasedOnRole()
    {
        if (Auth::user()->isSuperAdmin()) {
            return redirect()->route('superadmin.dashboard');
        }
        return redirect()->route('dashboard');
    }

    public function showLoginForm()
    {
        if (auth()->check()) {
            return $this->redirectBasedOnRole();
        }

        $businesses = collect();
        return view('auth.login', compact('businesses'));
    }

    public function showSignupForm()
    {
        if (auth()->check()) {
            return $this->redirectBasedOnRole();
        }

        return view('auth.signup');
    }

    public function signUp(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'subscription' => 'trial',
            'subscription_billing_start' => Carbon::now(),
            'subscription_billing_end' => Carbon::now()->addMonth(),
            'uuid' => Str::uuid(),
            'email_verified' => false,
            'first_name' => explode(' ', $request->name)[0],
            'last_name' => count(explode(' ', $request->name)) > 1 ? explode(' ', $request->name)[1] : ''
        ]);

        Auth::login($user);
        Log::info('New user registered', ['user_id' => $user->id, 'email' => $user->email]);

        return $this->redirectBasedOnRole();
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'This email is not registered. Please sign up.',
            ])->withInput($request->except('password'));
        }

        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            $request->session()->regenerate();
            Log::info('User logged in', ['user_id' => Auth::id(), 'email' => $request->email]);

            return $this->redirectBasedOnRole();
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User logged out', ['user_id' => $userId]);

        return redirect()->route('login')
            ->with('status', 'You have been logged out successfully.');
    }

    public function createSuperAdmin()
    {
        try {
            $superadmin = User::where('email', 'superadmin@mail.com')->first();

            if (!$superadmin) {
                $superadmin = User::create([
                    'name' => 'Super Admin',
                    'email' => 'superadmin@mail.com',
                    'password' => Hash::make('superadmin123'),
                    'role' => 'superadmin',
                    'subscription' => null,
                    'email_verified' => true,
                    'first_name' => 'Super',
                    'last_name' => 'Admin',
                    'uuid' => Str::uuid()
                ]);

                Log::info('Superadmin created successfully', [
                    'id' => $superadmin->id,
                    'email' => $superadmin->email,
                    'role' => $superadmin->role
                ]);

                return response()->json([
                    'message' => 'Superadmin created successfully',
                    'email' => 'superadmin@mail.com',
                    'password' => 'superadmin123'
                ]);
            }

            return response()->json([
                'message' => 'Superadmin already exists',
                'user' => $superadmin
            ]);

        } catch (Exception $e) {
            Log::error('Error creating superadmin: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error creating superadmin',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    protected function sendFailedLoginResponse($request, $message = null)
    {
        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => $message ?? 'These credentials do not match our records.']);
    }

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ]);

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}