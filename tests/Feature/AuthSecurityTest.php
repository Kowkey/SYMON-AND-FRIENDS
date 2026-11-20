<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Requirement 1: Login Attempt Limit
     * Locks users out after 5 consecutive failed login attempts.
     */
    public function test_user_is_locked_out_after_five_failed_login_attempts(): void
    {
        $user = User::factory()->create([
            'email'    => 'kevin.test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        RateLimiter::clear('kevin.test@example.com|127.0.0.1');

        // Attempt 1 to 5 with wrong password
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/login', [
                'email'    => 'kevin.test@example.com',
                'password' => 'wrong-password-' . $i,
            ]);

            $response->assertSessionHasErrors('email');
            $this->assertGuest();
        }

        // Attempt 6: should be locked out
        $response = $this->post('/login', [
            'email'    => 'kevin.test@example.com',
            'password' => 'even-correct-password-is-blocked-now',
        ]);

        $response->assertSessionHasErrors('email');
        $errorMessage = session('errors')->first('email');
        $this->assertStringContainsString('locked', $errorMessage);
        $this->assertGuest();
    }

    /**
     * Requirement 1: Rate limiter clears on successful login.
     */
    public function test_rate_limiter_is_cleared_after_successful_login(): void
    {
        $user = User::factory()->create([
            'email'    => 'kevin.success@example.com',
            'password' => bcrypt('secret123'),
        ]);

        RateLimiter::clear('kevin.success@example.com|127.0.0.1');

        // 2 failed attempts
        $this->post('/login', [
            'email'    => 'kevin.success@example.com',
            'password' => 'wrong1',
        ]);
        $this->post('/login', [
            'email'    => 'kevin.success@example.com',
            'password' => 'wrong2',
        ]);

        // Successful 3rd attempt
        $response = $this->post('/login', [
            'email'    => 'kevin.success@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Requirement 2: Authentication Protection
     * Guests cannot access the main page without logging in.
     */
    public function test_unauthenticated_users_cannot_access_main_dashboard(): void
    {
        $response = $this->get('/dashboard');

        // Must redirect to login
        $response->assertRedirect('/login');
    }

    /**
     * Requirement 2: Authentication Protection
     * Authenticated users cannot return to login or registration page.
     */
    public function test_authenticated_users_are_redirected_away_from_login_and_register(): void
    {
        $user = User::factory()->create();

        // Trying to access login
        $loginResponse = $this->actingAs($user)->get('/login');
        $loginResponse->assertRedirect('/dashboard');

        // Trying to access register
        $registerResponse = $this->actingAs($user)->get('/register');
        $registerResponse->assertRedirect('/dashboard');
    }

    /**
     * Requirement 3 & 4: Registration, One-to-One Profile & Default Role,
     * and Post/Redirect/Get pattern preventing form resubmission.
     */
    public function test_user_registration_creates_profile_and_redirects(): void
    {
        Role::firstOrCreate(['name' => 'Member']);

        $response = $this->post('/register', [
            'name'                  => 'Sir Kevin',
            'email'                 => 'sir.kevin@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'phone_number'          => '+63 912 345 6789',
            'bio'                   => 'Full stack developer',
        ]);

        // PRG: Must redirect, not render view directly
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        // Database assertions
        $this->assertDatabaseHas('users', ['email' => 'sir.kevin@example.com']);
        $user = User::where('email', 'sir.kevin@example.com')->first();

        // 1:1 Profile verified
        $this->assertNotNull($user->profile);
        $this->assertEquals('+63 912 345 6789', $user->profile->phone_number);

        // N:M Role verified
        $this->assertTrue($user->roles->contains('name', 'Member'));
    }

    /**
     * Requirement 4: Prevent Form Resubmission (Post/Redirect/Get)
     * Adding a post redirects to GET dashboard, so refreshing does not duplicate post.
     */
    public function test_creating_post_uses_post_redirect_get_and_prevents_duplicate(): void
    {
        $user = User::factory()->create();

        // 1. Submit form (POST)
        $postResponse = $this->actingAs($user)->post('/dashboard/posts', [
            'title' => 'First Post on PRG',
            'body'  => 'Demonstrating Post/Redirect/Get pattern.',
        ]);

        // Verify HTTP 302 redirect (PRG pattern)
        $postResponse->assertRedirect('/dashboard');
        $this->assertDatabaseCount('posts', 1);

        // 2. User simulates page refresh (GET /dashboard)
        $refreshResponse = $this->actingAs($user)->get('/dashboard');
        $refreshResponse->assertStatus(200);

        // Post count remains 1, no duplicate created
        $this->assertDatabaseCount('posts', 1);
    }

    /**
     * Requirement 3: Server-side debounce blocks duplicate burst submissions.
     */
    public function test_server_blocks_immediate_burst_duplicate_post_submission(): void
    {
        $user = User::factory()->create();

        // First submit succeeds
        $first = $this->actingAs($user)->post('/dashboard/posts', [
            'title' => 'Identical Rapid Title',
            'body'  => 'Rapid click test body.',
        ]);
        $first->assertRedirect('/dashboard');
        $this->assertDatabaseCount('posts', 1);

        // Immediate second submit (same title within 3 seconds) gets debounced
        $second = $this->actingAs($user)->post('/dashboard/posts', [
            'title' => 'Identical Rapid Title',
            'body'  => 'Rapid click test body.',
        ]);
        $second->assertRedirect('/dashboard');
        $second->assertSessionHas('warning');

        // Still exactly 1 post in database
        $this->assertDatabaseCount('posts', 1);
    }
}
