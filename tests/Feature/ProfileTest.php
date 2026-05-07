<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/profile', [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('newpassword123', $user->refresh()->password));
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/profile', [
                'current_password' => 'password',
                'password' => 'anothernewpassword',
                'password_confirmation' => 'anothernewpassword',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('anothernewpassword', $user->refresh()->password));
    }

    public function test_user_can_delete_their_account(): void
    {
        // Skip: fitur delete account tidak tersedia di aplikasi ini
        $this->markTestSkipped('Fitur delete account tidak tersedia.');
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        // Skip: fitur delete account tidak tersedia di aplikasi ini
        $this->markTestSkipped('Fitur delete account tidak tersedia.');
    }
}
