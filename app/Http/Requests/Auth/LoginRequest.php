<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'], // Ganti dari email ke username
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = \App\Models\User::where('username', $this->input('username'))->first();

        // Cek jika akun sudah terkunci duluan sebelum mencoba login
        if ($user && $user->failed_login_attempts >= 5) {
            throw ValidationException::withMessages([
                'username' => 'Akun Anda telah dikunci karena gagal login 5 kali. Silakan hubungi Admin untuk melakukan Reset Password.',
            ]);
        }

        if (! Auth::attempt($this->only('username', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Tambah failed login attempts
            if ($user) {
                $user->increment('failed_login_attempts');
                if ($user->failed_login_attempts >= 5) {
                    throw ValidationException::withMessages([
                        'username' => 'Akun Anda telah dikunci karena gagal login 5 kali. Silakan hubungi Admin untuk melakukan Reset Password.',
                    ]);
                }
            }

            // 🔐 Catat percobaan login gagal untuk security monitoring
            Log::warning('🔐 Login gagal', [
                'username' => $this->input('username'),
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'time' => now()->toDateTimeString(),
            ]);

            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        // --- SATPAM STATUS AKTIF ---
        if (! Auth::user()->is_active) {
            Auth::logout(); // Tendang keluar lagi

            throw ValidationException::withMessages([
                'username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi HR atau Admin.',
            ]);
        }
        // ----------------------------------------------

        // Reset failed_login_attempts jika berhasil masuk
        if (Auth::user()->failed_login_attempts > 0) {
            Auth::user()->update(['failed_login_attempts' => 0]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')) . '|' . $this->ip());
    }
}
