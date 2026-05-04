<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

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