<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Maximum allowed consecutive failed login attempts before lockout.
     */
    protected const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Lockout duration in seconds (1 minute / 60 seconds).
     */
    protected const LOCKOUT_SECONDS = 60;

    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt with rate limiting (brute force protection).
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = $this->throttleKey($request);

        // 1. Check if the user/IP is locked out after 5 failed attempts
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Your account is temporarily locked due to too many failed login attempts. Please wait {$seconds} seconds before trying again.",
            ])->onlyInput('email');
        }

        $remember = $request->boolean('remember');

        // 2. Attempt login
        if (Auth::attempt($credentials, $remember)) {
            // Success: clear rate limiter counter & regenerate session to prevent session fixation
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            // Requirement 4: Post/Redirect/Get pattern
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . Auth::user()->name . '!');
        }

        // 3. Failed attempt: increment the rate limiter counter
        RateLimiter::hit($throttleKey, self::LOCKOUT_SECONDS);
        $remaining = RateLimiter::remaining($throttleKey, self::MAX_LOGIN_ATTEMPTS);

        if ($remaining > 0) {
            $errorMessage = "Invalid credentials. You have {$remaining} attempt(s) remaining before temporary lockout.";
        } else {
            $errorMessage = "Invalid credentials. You have reached the maximum limit of " . self::MAX_LOGIN_ATTEMPTS . " failed attempts. Your account has been temporarily locked for " . self::LOCKOUT_SECONDS . " seconds.";
        }

        return back()->withErrors(['email' => $errorMessage])->onlyInput('email');
    }

    /**
     * Show the user registration form.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle user registration with Profile (1:1) and Default Role (N:M).
     */
    public function register(Request $request): RedirectResponse
    {
        // Server-side duplicate submission guard (debounce burst clicks)
        $submissionKey = 'reg_submit_' . $request->ip() . '_' . md5($request->input('email', ''));
        if (!cache()->add($submissionKey, true, 5)) {
            return back()->withErrors([
                'email' => 'A submission is already processing. Please do not click repeatedly.',
            ])->onlyInput('name', 'email');
        }

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'bio'          => ['nullable', 'string', 'max:500'],
        ]);

        // 1. Create User
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // 2. Create One-to-One Profile
        $user->profile()->create([
            'phone_number' => $validated['phone_number'] ?? null,
            'bio'          => $validated['bio'] ?? 'Welcome to my profile!',
        ]);

        // 3. Assign Default Role (Many-to-Many)
        $memberRole = Role::firstOrCreate(['name' => 'Member']);
        $user->roles()->syncWithoutDetaching([$memberRole->id]);

        // 4. Log in the user
        Auth::login($user);
        $request->session()->regenerate();

        // Requirement 4: Post/Redirect/Get pattern
        return redirect()->route('dashboard')
            ->with('success', 'Registration successful! Your account and profile have been created.');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been successfully logged out.');
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email', '')) . '|' . $request->ip());
    }
}
