<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SanctumTokenManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

class SanctumTokenManagerTest extends TestCase
{
    use RefreshDatabase;

    protected SanctumTokenManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new SanctumTokenManager();
    }

    /**
     * Test token generation for any user
     * 
     * @test
     */
    public function test_generates_token_for_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $token = $this->manager->generateToken($user);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        
        // Verify token format (should be in format: id|plainTextToken)
        $this->assertStringContainsString('|', $token);
    }

    /**
     * Test token generation with default token name
     * 
     * @test
     */
    public function test_generates_token_with_default_name()
    {
        $user = User::factory()->create();

        $token = $this->manager->generateToken($user);

        // Verify token was created in database with default name
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'firebase-auth',
        ]);
    }

    /**
     * Test token generation with custom token name
     * 
     * @test
     */
    public function test_generates_token_with_custom_name()
    {
        $user = User::factory()->create();

        $token = $this->manager->generateToken($user, 'custom-token-name');

        // Verify token was created with custom name
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'custom-token-name',
        ]);
    }

    /**
     * Test token has all abilities (*)
     * 
     * @test
     */
    public function test_generated_token_has_all_abilities()
    {
        $user = User::factory()->create();

        $token = $this->manager->generateToken($user);

        // Get the token from database
        $tokenRecord = PersonalAccessToken::where('tokenable_id', $user->id)
            ->where('tokenable_type', User::class)
            ->first();

        $this->assertNotNull($tokenRecord);
        $this->assertEquals(['*'], $tokenRecord->abilities);
    }

    /**
     * Test multiple tokens can be generated for same user
     * 
     * @test
     */
    public function test_generates_multiple_tokens_for_same_user()
    {
        $user = User::factory()->create();

        $token1 = $this->manager->generateToken($user, 'token-1');
        $token2 = $this->manager->generateToken($user, 'token-2');

        $this->assertNotEquals($token1, $token2);
        
        // Verify both tokens exist in database
        $this->assertEquals(2, $user->tokens()->count());
    }

    /**
     * Test token revocation for user
     * 
     * @test
     */
    public function test_revokes_all_tokens_for_user()
    {
        $user = User::factory()->create();

        // Generate multiple tokens
        $this->manager->generateToken($user, 'token-1');
        $this->manager->generateToken($user, 'token-2');
        $this->manager->generateToken($user, 'token-3');

        // Verify tokens exist
        $this->assertEquals(3, $user->tokens()->count());

        // Revoke all tokens
        $this->manager->revokeAllTokens($user);

        // Verify all tokens are deleted
        $this->assertEquals(0, $user->fresh()->tokens()->count());
    }

    /**
     * Test revoking tokens for user with no tokens
     * 
     * @test
     */
    public function test_revokes_tokens_for_user_with_no_tokens()
    {
        $user = User::factory()->create();

        // User has no tokens
        $this->assertEquals(0, $user->tokens()->count());

        // Should not throw error
        $this->manager->revokeAllTokens($user);

        // Still no tokens
        $this->assertEquals(0, $user->tokens()->count());
    }

    /**
     * Test revoking tokens only affects specific user
     * 
     * @test
     */
    public function test_revokes_tokens_only_for_specific_user()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // Generate tokens for both users
        $this->manager->generateToken($user1, 'user1-token');
        $this->manager->generateToken($user2, 'user2-token');

        // Verify both have tokens
        $this->assertEquals(1, $user1->tokens()->count());
        $this->assertEquals(1, $user2->tokens()->count());

        // Revoke tokens for user1 only
        $this->manager->revokeAllTokens($user1);

        // Verify only user1's tokens are revoked
        $this->assertEquals(0, $user1->fresh()->tokens()->count());
        $this->assertEquals(1, $user2->fresh()->tokens()->count());
    }

    /**
     * Test generated token can be used for authentication
     * 
     * @test
     */
    public function test_generated_token_can_be_used_for_authentication()
    {
        $user = User::factory()->create();

        $token = $this->manager->generateToken($user);

        // Use the token to authenticate
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');

        // Should be authenticated (or 404 if route doesn't exist, but not 401)
        $this->assertNotEquals(401, $response->status());
    }

    /**
     * Test token generation returns plain text token
     * 
     * @test
     */
    public function test_returns_plain_text_token()
    {
        $user = User::factory()->create();

        $token = $this->manager->generateToken($user);

        // Plain text token should be a string with pipe separator
        $this->assertIsString($token);
        $parts = explode('|', $token);
        $this->assertCount(2, $parts);
        
        // First part should be numeric (token ID)
        $this->assertIsNumeric($parts[0]);
        
        // Second part should be the actual token (long string)
        $this->assertGreaterThan(40, strlen($parts[1]));
    }

    /**
     * Test token naming convention
     * 
     * @test
     */
    public function test_token_naming_convention()
    {
        $user = User::factory()->create();

        $token = $this->manager->generateToken($user);

        // Verify the token name in database
        $tokenRecord = $user->tokens()->first();
        $this->assertEquals('firebase-auth', $tokenRecord->name);
    }

    /**
     * Test token abilities configuration
     * 
     * @test
     */
    public function test_token_abilities_configuration()
    {
        $user = User::factory()->create();

        $token = $this->manager->generateToken($user);

        // Get token record and verify abilities
        $tokenRecord = $user->tokens()->first();
        
        // Should have all abilities (*)
        $this->assertIsArray($tokenRecord->abilities);
        $this->assertContains('*', $tokenRecord->abilities);
    }

    /**
     * Test token generation for different user types
     * 
     * @test
     */
    public function test_generates_tokens_for_different_user_roles()
    {
        $nasabah = User::factory()->create(['role' => 'nasabah']);
        $admin = User::factory()->create(['role' => 'admin']);
        $mitra = User::factory()->create(['role' => 'mitra']);

        $tokenNasabah = $this->manager->generateToken($nasabah);
        $tokenAdmin = $this->manager->generateToken($admin);
        $tokenMitra = $this->manager->generateToken($mitra);

        // All should generate valid tokens
        $this->assertIsString($tokenNasabah);
        $this->assertIsString($tokenAdmin);
        $this->assertIsString($tokenMitra);

        // Verify all tokens are in database
        $this->assertEquals(1, $nasabah->tokens()->count());
        $this->assertEquals(1, $admin->tokens()->count());
        $this->assertEquals(1, $mitra->tokens()->count());
    }

    /**
     * Test token generation with Firebase user data
     * 
     * @test
     */
    public function test_generates_token_for_firebase_user()
    {
        $user = User::factory()->create([
            'email' => 'firebase@example.com',
            'firebase_uid' => 'firebase-uid-123',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ]);

        $token = $this->manager->generateToken($user);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        
        // Verify token is associated with the Firebase user
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }
}
