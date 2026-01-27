<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FirebaseAuthService;
use App\Services\InvalidTokenException;
use App\Services\SecurityException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

/**
 * Integration tests for FirebaseAuthController
 * 
 * Tests the full authentication flow from HTTP request to response,
 * including coordination between FirebaseAuthService, UserProfileManager,
 * and SanctumTokenManager.
 * 
 * Requirements: 1.1, 1.3, 1.4, 1.5, 1.6, 1.7, 5.1, 5.2, 5.3
 */
class FirebaseAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test successful authentication with valid token for new user
     * 
     * Requirements: 1.1, 1.3, 1.5, 1.6, 5.1, 5.2
     */
    public function test_successful_authentication_creates_new_user()
    {
        // Mock FirebaseAuthService to return valid claims
        $this->mock(FirebaseAuthService::class, function ($mock) {
            $mock->shouldReceive('authenticateWithFirebase')
                ->once()
                ->with('valid.firebase.token')
                ->andReturn([
                    'uid' => 'firebase-uid-123',
                    'email' => 'newuser@example.com',
                    'name' => 'New User',
                    'picture' => 'https://example.com/avatar.jpg',
                ]);
        });

        // Send POST request to Firebase auth endpoint
        $response = $this->postJson('/api/auth/firebase/google', [
            'id_token' => 'valid.firebase.token',
        ]);

        // Assert response structure (Requirement 5.1, 5.2)
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'avatar_url',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login success',
                'data' => [
                    'token_type' => 'Bearer',
                ],
            ]);

        // Assert user was created in database (Requirement 1.3)
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
            'firebase_uid' => 'firebase-uid-123',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ]);

        // Assert token is valid (Requirement 1.5)
        $token = $response->json('data.token');
        $this->assertNotEmpty($token);
    }

    /**
     * Test successful authentication with valid token for existing user
     * 
     * Requirements: 1.1, 1.4, 1.5, 1.6, 5.1, 5.2
     */
    public function test_successful_authentication_updates_existing_user()
    {
        // Create existing user
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Old Name',
            'firebase_uid' => null,
            'avatar_url' => null,
        ]);

        // Mock FirebaseAuthService to return valid claims
        $this->mock(FirebaseAuthService::class, function ($mock) {
            $mock->shouldReceive('authenticateWithFirebase')
                ->once()
                ->with('valid.firebase.token')
                ->andReturn([
                    'uid' => 'firebase-uid-456',
                    'email' => 'existing@example.com',
                    'name' => 'Updated Name',
                    'picture' => 'https://example.com/new-avatar.jpg',
                ]);
        });

        // Send POST request to Firebase auth endpoint
        $response = $this->postJson('/api/auth/firebase/google', [
            'id_token' => 'valid.firebase.token',
        ]);

        // Assert response structure
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login success',
                'data' => [
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $existingUser->id,
                        'email' => 'existing@example.com',
                        'name' => 'Updated Name',
                        'avatar_url' => 'https://example.com/new-avatar.jpg',
                    ],
                ],
            ]);

        // Assert user was updated (Requirement 1.4)
        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'email' => 'existing@example.com',
            'name' => 'Updated Name',
            'firebase_uid' => 'firebase-uid-456',
            'avatar_url' => 'https://example.com/new-avatar.jpg',
        ]);

        // Assert only one user exists (no duplicate created)
        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());
    }

    /**
     * Test authentication fails with invalid token
     * 
     * Requirements: 1.7, 5.4
     */
    public function test_authentication_fails_with_invalid_token()
    {
        // Mock FirebaseAuthService to throw InvalidTokenException
        $this->mock(FirebaseAuthService::class, function ($mock) {
            $mock->shouldReceive('authenticateWithFirebase')
                ->once()
                ->with('invalid.token')
                ->andThrow(new InvalidTokenException('Invalid Firebase token'));
        });

        // Send POST request with invalid token
        $response = $this->postJson('/api/auth/firebase/google', [
            'id_token' => 'invalid.token',
        ]);

        // Assert 401 response (Requirement 1.7)
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid or expired Firebase token',
            ]);

        // Assert no user was created
        $this->assertEquals(0, User::count());
    }

    /**
     * Test authentication fails with security violation
     * 
     * Requirements: 1.8, 6.3, 5.4
     */
    public function test_authentication_fails_with_security_violation()
    {
        // Mock FirebaseAuthService to throw SecurityException
        $this->mock(FirebaseAuthService::class, function ($mock) {
            $mock->shouldReceive('authenticateWithFirebase')
                ->once()
                ->andThrow(new SecurityException('APP_DEBUG must be false in production'));
        });

        // Send POST request
        $response = $this->postJson('/api/auth/firebase/google', [
            'id_token' => 'any.token',
        ]);

        // Assert 500 response (Requirement 1.8)
        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Security configuration error',
            ]);
    }

    /**
     * Test validation fails when id_token is missing
     * 
     * Requirements: 1.1, 5.4
     */
    public function test_validation_fails_when_id_token_missing()
    {
        // Mock FirebaseAuthService to avoid initialization issues
        $this->mock(FirebaseAuthService::class, function ($mock) {
            // No expectations - validation should fail before service is called
        });

        // Send POST request without id_token
        $response = $this->postJson('/api/auth/firebase/google', []);

        // Assert 422 validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_token']);
    }

    /**
     * Test validation fails when id_token is not a string
     * 
     * Requirements: 1.1, 5.4
     */
    public function test_validation_fails_when_id_token_not_string()
    {
        // Mock FirebaseAuthService to avoid initialization issues
        $this->mock(FirebaseAuthService::class, function ($mock) {
            // No expectations - validation should fail before service is called
        });

        // Send POST request with non-string id_token
        $response = $this->postJson('/api/auth/firebase/google', [
            'id_token' => 12345,
        ]);

        // Assert 422 validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_token']);
    }

    /**
     * Test Sanctum token can be used for subsequent API calls
     * 
     * Requirements: 1.5, 1.6
     */
    public function test_sanctum_token_works_for_api_calls()
    {
        // Mock FirebaseAuthService
        $this->mock(FirebaseAuthService::class, function ($mock) {
            $mock->shouldReceive('authenticateWithFirebase')
                ->once()
                ->andReturn([
                    'uid' => 'firebase-uid-789',
                    'email' => 'apitest@example.com',
                    'name' => 'API Test User',
                    'picture' => null,
                ]);
        });

        // Authenticate and get token
        $authResponse = $this->postJson('/api/auth/firebase/google', [
            'id_token' => 'valid.token',
        ]);

        $token = $authResponse->json('data.token');

        // Use token to access protected endpoint
        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        // Assert token works
        $profileResponse->assertStatus(200);
    }
}
