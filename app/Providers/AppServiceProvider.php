<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Domain\Rules\ApprovalRule;
use App\Domain\Rules\DeclarationRule;
use App\Models\RiskReport;
use App\Policies\RiskReportPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Domain Rule bindings — singleton agar instance yang sama dipakai di semua service
        $this->app->singleton(ApprovalRule::class, function () {
            return new ApprovalRule();
        });

        $this->app->singleton(DeclarationRule::class, function () {
            return new DeclarationRule();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan Policy
        Gate::policy(RiskReport::class, RiskReportPolicy::class);

        // Maksa Laravel pakai HTTPS kalau bukan di XAMPP lokal
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // Custom validasi: minimal jumlah kata
        Validator::extend('min_words', function ($attribute, $value, $parameters, $validator) {
            $min = (int) ($parameters[0] ?? 1);
            $wordCount = count(preg_split('/\s+/', trim($value)));
            return $wordCount >= $min;
        }, 'Kolom :attribute harus minimal :min kata.');

        // ================================================================
        // RATE LIMITER: Named Rate Limiters + Logging
        // ================================================================

        // Global rate limiter — dipasang di semua route via middleware
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(65)->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    Log::warning('⚠️ Rate limit global terkena!', [
                        'user_id' => auth()->id(),
                        'username' => auth()->user()?->username,
                        'ip' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'time' => now()->toDateTimeString(),
                    ]);

                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak permintaan. Silakan coba lagi dalam 1 menit.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });

        // Login rate limiter — khusus untuk percobaan login
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->input('username') . '|' . $request->ip())
                ->response(function ($request, $headers) {
                    Log::warning('🔐 Rate limit login terkena!', [
                        'username' => $request->input('username'),
                        'ip' => $request->ip(),
                        'time' => now()->toDateTimeString(),
                    ]);

                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });

        // Export rate limiter — khusus export CSV (berat)
        RateLimiter::for('export', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    Log::warning('📊 Rate limit export terkena!', [
                        'user_id' => auth()->id(),
                        'username' => auth()->user()?->username,
                        'ip' => $request->ip(),
                        'time' => now()->toDateTimeString(),
                    ]);

                    return back()->with('error', 'Terlalu banyak permintaan export. Silakan coba lagi dalam 1 menit.');
                });
        });

        // Admin rate limiter — khusus panel admin
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(25)->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    Log::warning('🛡️ Rate limit admin terkena!', [
                        'user_id' => auth()->id(),
                        'username' => auth()->user()?->username,
                        'ip' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'time' => now()->toDateTimeString(),
                    ]);

                    return back()->with('error', 'Terlalu banyak permintaan. Silakan coba lagi dalam 1 menit.');
                });
        });

        // ================================================================
        // NAMED RATE LIMITERS — per-user (by user_id) untuk semua route
        // ================================================================

        // Dashboard — 35x/menit per user
        RateLimiter::for('dashboard', function (Request $request) {
            return Limit::perMinute(35)->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak permintaan. Silakan coba lagi.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });

        // Profile update — 10x/menit per user
        RateLimiter::for('profile', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak permintaan. Silakan coba lagi.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });

        // Store laporan — 15x/menit per user
        RateLimiter::for('store_report', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak permintaan. Silakan coba lagi.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });

        // Approval/Reject — 15x/menit per user
        RateLimiter::for('approval', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak permintaan. Silakan coba lagi.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });

        // Resolution (tindak lanjut) — 15x/menit per user
        RateLimiter::for('resolution', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak permintaan. Silakan coba lagi.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });

        // Progress catatan — 15x/menit per user
        RateLimiter::for('progress', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak permintaan. Silakan coba lagi.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });

        // Revisi laporan — 15x/menit per user (shared counter untuk request/submit/approve)
        RateLimiter::for('revision', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak permintaan. Silakan coba lagi.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });

        // Deklarasi nihil — 15x/menit per user
        RateLimiter::for('deklarasi_nihil', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    return response()->view('errors.429', [
                        'message' => 'Terlalu banyak permintaan. Silakan coba lagi.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429);
                });
        });
    }
}
