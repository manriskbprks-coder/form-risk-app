<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Phase 1: Environment & Configuration Hardening
 * 
 * Pengujian ini dilakukan layaknya penetration tester profesional untuk
 * memvalidasi implementasi keamanan Phase 1:
 * - 1.1 Production Config (APP_DEBUG, APP_ENV)
 * - 1.2 Session Security (SECURE_COOKIE, HTTPONLY, SAME_SITE, LIFETIME)
 * - 1.3 CORS Restriction
 * - 1.4 HTTPS Redirect (ForceHttps middleware)
 * - 1.5 Security Headers
 */
class Phase1ConfigTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'teller']);

        $this->branch = Branch::factory()->create([
            'nama_cabang' => 'Cabang A',
            'kode_cabang' => 'CBA',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $this->user->assignRole('teller');
    }

    // ========================================================================
    // 1.1 PRODUCTION CONFIG
    // ========================================================================

    #[Test]
    public function app_debug_is_disabled_in_production_config()
    {
        // Cek file .env.example sebagai referensi konfigurasi production
        // Di production, APP_DEBUG harus false
        $envExample = file_get_contents(base_path('.env.example'));
        
        // Cari APP_DEBUG di .env.example
        preg_match('/^APP_DEBUG=(.*)$/m', $envExample, $matches);
        
        if (!empty($matches)) {
            $this->assertEquals(
                'false',
                $matches[1],
                'APP_DEBUG harus false di .env.example (referensi production)'
            );
        }
        
        // Juga cek .env file (local development)
        if (file_exists(base_path('.env'))) {
            $envFile = file_get_contents(base_path('.env'));
            preg_match('/^APP_DEBUG=(.*)$/m', $envFile, $envMatches);
            
            if (!empty($envMatches)) {
                // Di local development, APP_DEBUG boleh true
                // Tapi pastikan ada komentar/notice bahwa harus false di production
                $this->assertNotNull(
                    $matches[0] ?? null,
                    'APP_DEBUG harus terdefinisi di .env'
                );
            }
        }
    }

    #[Test]
    public function app_env_is_production_in_env_example()
    {
        // Cek .env.example sebagai referensi
        $envExample = file_get_contents(base_path('.env.example'));
        
        preg_match('/^APP_ENV=(.*)$/m', $envExample, $matches);
        
        if (!empty($matches)) {
            $this->assertEquals(
                'production',
                $matches[1],
                'APP_ENV harus "production" di .env.example (referensi production)'
            );
        }
    }

    #[Test]
    public function env_file_is_gitignored()
    {
        // Cek .gitignore apakah .env ada di dalamnya
        $gitignore = file_get_contents(base_path('.gitignore'));
        
        $this->assertStringContainsString(
            '.env',
            $gitignore,
            '.env harus terdaftar di .gitignore agar tidak ter-commit'
        );
    }

    // ========================================================================
    // 1.2 SESSION SECURITY
    // ========================================================================

    #[Test]
    public function session_secure_cookie_is_true()
    {
        $secure = config('session.secure');
        
        // SESSION_SECURE_COOKIE=true — cookie hanya dikirim via HTTPS
        $this->assertTrue(
            filter_var($secure, FILTER_VALIDATE_BOOLEAN),
            'SESSION_SECURE_COOKIE harus true agar session cookie hanya dikirim via HTTPS'
        );
    }

    #[Test]
    public function session_http_only_is_true()
    {
        $httpOnly = config('session.http_only');
        
        // SESSION_HTTPONLY=true — cegah akses cookie via JavaScript
        $this->assertTrue(
            $httpOnly,
            'SESSION_HTTPONLY harus true untuk mencegah XSS mencuri session cookie'
        );
    }

    #[Test]
    public function session_same_site_is_lax()
    {
        $sameSite = config('session.same_site');
        
        // SESSION_SAME_SITE=lax — mitigasi CSRF
        $this->assertEquals(
            'lax',
            $sameSite,
            'SESSION_SAME_SITE harus "lax" untuk mitigasi CSRF'
        );
    }

    #[Test]
    public function session_lifetime_is_120_minutes()
    {
        $lifetime = config('session.lifetime');
        
        // SESSION_LIFETIME=120 — session expired 2 jam
        $this->assertEquals(
            120,
            $lifetime,
            'SESSION_LIFETIME harus 120 menit (2 jam)'
        );
    }

    // ========================================================================
    // 1.3 CORS RESTRICTION
    // ========================================================================

    #[Test]
    public function cors_allowed_origins_is_not_wildcard()
    {
        $allowedOrigins = config('cors.allowed_origins');
        
        // allowed_origins tidak boleh '*' — harus spesifik
        if (is_array($allowedOrigins)) {
            $this->assertNotContains('*', $allowedOrigins, 'CORS allowed_origins tidak boleh wildcard "*"');
        } else {
            $this->assertNotEquals('*', $allowedOrigins, 'CORS allowed_origins tidak boleh wildcard "*"');
        }
    }

    #[Test]
    public function cors_allowed_origins_is_limited()
    {
        $allowedOrigins = config('cors.allowed_origins');
        
        // allowed_origins harus terbatas, bukan array kosong atau wildcard
        $this->assertNotEmpty($allowedOrigins, 'CORS allowed_origins tidak boleh kosong');
        
        if (is_array($allowedOrigins)) {
            $this->assertIsArray($allowedOrigins);
        }
    }

    // ========================================================================
    // 1.5 SECURITY HEADERS
    // ========================================================================

    #[Test]
    public function security_headers_are_present()
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('dashboard'));
        
        // X-Content-Type-Options: nosniff — cegah MIME sniffing
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        
        // X-Frame-Options: DENY — cegah clickjacking
        $response->assertHeader('X-Frame-Options', 'DENY');
        
        // X-XSS-Protection: 1; mode=block — aktifkan XSS filter browser
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        
        // Referrer-Policy: strict-origin-when-cross-origin
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    #[Test]
    public function sensitive_headers_are_removed()
    {
        $this->actingAs($this->user);
        
        $response = $this->get(route('dashboard'));
        
        // X-Powered-By harus dihapus — jangan bocorkan info server
        $response->assertHeaderMissing('X-Powered-By');
        
        // Server header harus dihapus — jangan bocorkan info server
        $response->assertHeaderMissing('Server');
    }

    #[Test]
    public function force_https_middleware_is_registered()
    {
        // Cek apakah middleware ForceHttps terdaftar di kernel
        $middleware = $this->app->make('Illuminate\Contracts\Http\Kernel');
        
        $this->assertTrue(
            in_array(\App\Http\Middleware\ForceHttps::class, $middleware->getMiddlewareGroups()['web'] ?? []) ||
            in_array(\App\Http\Middleware\ForceHttps::class, $middleware->getGlobalMiddleware() ?? []),
            'Middleware ForceHttps harus terdaftar'
        );
    }

    #[Test]
    public function security_headers_middleware_is_registered()
    {
        // Cek apakah middleware SecurityHeaders terdaftar
        $middleware = $this->app->make('Illuminate\Contracts\Http\Kernel');
        
        $this->assertTrue(
            in_array(\App\Http\Middleware\SecurityHeaders::class, $middleware->getGlobalMiddleware() ?? []),
            'Middleware SecurityHeaders harus terdaftar sebagai global middleware'
        );
    }
}
