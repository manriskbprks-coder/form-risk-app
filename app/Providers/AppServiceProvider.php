<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use App\Models\RiskReport;
use App\Policies\RiskReportPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
    }
}
