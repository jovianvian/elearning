<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $loginInput = trim($validated['login']);
        $password = $validated['password'];
        $remember = (bool) ($validated['remember'] ?? false);

        [$user, $identifierType] = $this->resolveUserAndIdentifier($loginInput);

        if (! $user || ! $this->isIdentifierAllowedByRole($user, $identifierType)) {
            $this->logLoginAttempt($user, false, __('auth.failed_identifier'));

            return back()->withErrors(['login' => __('auth.failed')])->onlyInput('login');
        }

        try {
            $passwordMatches = Hash::check($password, $user->password);
        } catch (\RuntimeException) {
            $this->logLoginAttempt($user, false, __('auth.failed'));

            return back()->withErrors(['login' => __('auth.failed')])->onlyInput('login');
        }

        if (! $passwordMatches) {
            $this->logLoginAttempt($user, false, __('auth.failed'));

            return back()->withErrors(['login' => __('auth.failed')])->onlyInput('login');
        }

        if (! $user->is_active || $user->blocked_at !== null) {
            $this->logLoginAttempt($user, false, __('auth.account_inactive'));

            return back()->withErrors(['login' => __('auth.account_inactive')])->onlyInput('login');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        $this->logLoginAttempt($user, true, null, $request->session()->getId(), $request);

        if ($user->must_change_password) {
            return redirect()->route('password.force.form');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function showForceChangePassword(): View
    {
        abort_unless(auth()->check(), 403);

        return view('auth.force-change-password');
    }

    public function forceChangePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->string('password')->toString()),
            'must_change_password' => false,
        ]);

        return redirect()->route('dashboard')->with('success', __('auth.password_forced_changed'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $sessionId = $request->session()->getId();
        $userId = auth()->id();

        LoginLog::where('session_id', $sessionId)
            ->where('user_id', $userId)
            ->whereNull('logout_at')
            ->latest('id')
            ->first()?->update(['logout_at' => now()]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function resolveUserAndIdentifier(string $loginInput): array
    {
        $user = User::with('role')
            ->where('nis', $loginInput)
            ->orWhere('nip', $loginInput)
            ->orWhere('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if (! $user) {
            return [null, null];
        }

        $identifierType = match (true) {
            $user->nis === $loginInput => 'nis',
            $user->nip === $loginInput => 'nip',
            $user->username === $loginInput => 'username',
            $user->email === $loginInput => 'email',
            default => null,
        };

        return [$user, $identifierType];
    }

    private function isIdentifierAllowedByRole(User $user, ?string $identifierType): bool
    {
        if (! $user->role || ! $identifierType) {
            return false;
        }

        return match ($user->role->code) {
            // Keep NIS/NIP as primary identifiers, but allow username/email fallback
            // to reduce failed logins caused by identifier confusion.
            Role::STUDENT => in_array($identifierType, ['nis', 'username', 'email'], true),
            Role::TEACHER => in_array($identifierType, ['nip', 'username', 'email'], true),
            Role::ADMIN, Role::SUPER_ADMIN, Role::PRINCIPAL => in_array($identifierType, ['username', 'email'], true),
            default => false,
        };
    }

    private function logLoginAttempt(?User $user, bool $isSuccess, ?string $failureReason = null, ?string $sessionId = null, ?Request $request = null): void
    {
        LoginLog::create([
            'user_id' => $user?->id,
            'login_at' => now(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'is_success' => $isSuccess,
            'failure_reason' => $failureReason,
            'session_id' => $sessionId,
        ]);
    }
}
