<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UserProfileManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Exception;

class UserProfileManagerTest extends TestCase
{
    use RefreshDatabase;

    protected UserProfileManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new UserProfileManager();
    }

    /**
     * Test creating a new user from Firebase claims
     * 
     * @test
     */
    public function test_creates_new_user_from_firebase_claims()
    {
        $claims = [
            'uid' => 'firebase-uid-123',
            'email' => 'newuser@example.com',
            'name' => 'New User',
            'picture' => 'https://example.com/avatar.jpg',
        ];

        $user = $this->manager->createOrUpdateFromFirebase($claims);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('newuser@example.com', $user->email);
        $this->assertEquals('New User', $user->name);
        $this->assertEquals('firebase-uid-123', $user->firebase_uid);
        $this->assertEquals('https://example.com/avatar.jpg', $user->avatar_url);
        $this->assertEquals('nasabah', $user->role); // Default role
        $this->assertNotNull($user->password); // Should have a password
    }

    /**
     * Test creating a new user without name defaults to 'User'
     * 
     * @test
     */
    public function test_creates_new_user_without_name_defaults_to_user()
    {
        $claims = [
            'uid' => 'firebase-uid-456',
            'email' => 'noname@example.com',
            'name' => null,
            'picture' => null,
        ];

        $user = $this->manager->createOrUpdateFromFirebase($claims);

        $this->assertEquals('User', $user->name);
        $this->assertNull($user->avatar_url);
    }

    /**
     * Test updating an existing user with Firebase claims
     * 
     * @test
     */
    public function test_updates_existing_user_with_firebase_claims()
    {
        // Create an existing user
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Old Name',
            'firebase_uid' => null,
            'avatar_url' => null,
        ]);

        $claims = [
            'uid' => 'firebase-uid-789',
            'email' => 'existing@example.com',
            'name' => 'Updated Name',
            'picture' => 'https://example.com/new-avatar.jpg',
        ];

        $user = $this->manager->createOrUpdateFromFirebase($claims);

        $this->assertEquals($existingUser->id, $user->id); // Same user
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('firebase-uid-789', $user->firebase_uid);
        $this->assertEquals('https://example.com/new-avatar.jpg', $user->avatar_url);
    }

    /**
     * Test updating existing user preserves name if not provided in claims
     * 
     * @test
     */
    public function test_updates_existing_user_preserves_name_if_not_provided()
    {
        // Create an existing user
        $existingUser = User::factory()->create([
            'email' => 'preserve@example.com',
            'name' => 'Original Name',
            'firebase_uid' => null,
            'avatar_url' => 'https://example.com/old-avatar.jpg',
        ]);

        $claims = [
            'uid' => 'firebase-uid-999',
            'email' => 'preserve@example.com',
            'name' => null,
            'picture' => null,
        ];

        $user = $this->manager->createOrUpdateFromFirebase($claims);

        $this->assertEquals('Original Name', $user->name); // Name preserved
        $this->assertEquals('firebase-uid-999', $user->firebase_uid); // UID updated
        $this->assertEquals('https://example.com/old-avatar.jpg', $user->avatar_url); // Avatar preserved
    }

    /**
     * Test that duplicate users are not created
     * 
     * @test
     */
    public function test_does_not_create_duplicate_users()
    {
        $claims = [
            'uid' => 'firebase-uid-duplicate',
            'email' => 'duplicate@example.com',
            'name' => 'Duplicate User',
            'picture' => null,
        ];

        // Create user first time
        $user1 = $this->manager->createOrUpdateFromFirebase($claims);
        $initialCount = User::count();

        // Try to create again with same email
        $user2 = $this->manager->createOrUpdateFromFirebase($claims);
        $finalCount = User::count();

        $this->assertEquals($initialCount, $finalCount); // No new user created
        $this->assertEquals($user1->id, $user2->id); // Same user returned
    }

    /**
     * Test findByEmail returns user when exists
     * 
     * @test
     */
    public function test_find_by_email_returns_user_when_exists()
    {
        $user = User::factory()->create([
            'email' => 'findme@example.com',
        ]);

        $reflection = new \ReflectionClass($this->manager);
        $method = $reflection->getMethod('findByEmail');
        $method->setAccessible(true);

        $foundUser = $method->invoke($this->manager, 'findme@example.com');

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }

    /**
     * Test findByEmail returns null when user does not exist
     * 
     * @test
     */
    public function test_find_by_email_returns_null_when_user_does_not_exist()
    {
        $reflection = new \ReflectionClass($this->manager);
        $method = $reflection->getMethod('findByEmail');
        $method->setAccessible(true);

        $foundUser = $method->invoke($this->manager, 'nonexistent@example.com');

        $this->assertNull($foundUser);
    }

    /**
     * Test that all Firebase fields are properly stored
     * 
     * @test
     */
    public function test_all_firebase_fields_are_stored()
    {
        $claims = [
            'uid' => 'complete-firebase-uid',
            'email' => 'complete@example.com',
            'name' => 'Complete User',
            'picture' => 'https://example.com/complete-avatar.jpg',
        ];

        $user = $this->manager->createOrUpdateFromFirebase($claims);

        // Verify all fields are in database
        $this->assertDatabaseHas('users', [
            'email' => 'complete@example.com',
            'name' => 'Complete User',
            'firebase_uid' => 'complete-firebase-uid',
            'avatar_url' => 'https://example.com/complete-avatar.jpg',
        ]);
    }

    /**
     * Test that user update refreshes the model
     * 
     * @test
     */
    public function test_user_update_returns_fresh_model()
    {
        $existingUser = User::factory()->create([
            'email' => 'refresh@example.com',
            'name' => 'Old Name',
        ]);

        $claims = [
            'uid' => 'firebase-uid-refresh',
            'email' => 'refresh@example.com',
            'name' => 'New Name',
            'picture' => null,
        ];

        $updatedUser = $this->manager->createOrUpdateFromFirebase($claims);

        // Verify the returned user has the updated data
        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('firebase-uid-refresh', $updatedUser->firebase_uid);
        
        // Verify the database has the updated data
        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'name' => 'New Name',
            'firebase_uid' => 'firebase-uid-refresh',
        ]);
    }

    /**
     * Test that transaction is rolled back on error
     * 
     * @test
     */
    public function test_transaction_rollback_on_error()
    {
        // Create a user with a specific email
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $claims = [
            'uid' => 'firebase-uid-error',
            'email' => 'test@example.com',
            'name' => 'Error User',
            'picture' => null,
        ];

        $initialCount = User::count();

        try {
            // Create a mock that will throw an exception during findByEmail
            $manager = \Mockery::mock(UserProfileManager::class)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
            
            $manager->shouldReceive('findByEmail')
                ->andThrow(new \Exception('Database connection error'));

            $manager->createOrUpdateFromFirebase($claims);
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            // Exception expected
            $this->assertStringContainsString('Failed to create or update user profile', $e->getMessage());
        }

        $finalCount = User::count();
        
        // Verify no additional user was created due to rollback
        $this->assertEquals($initialCount, $finalCount);
    }

    /**
     * Test creating user with minimal required fields
     * 
     * @test
     */
    public function test_creates_user_with_minimal_required_fields()
    {
        $claims = [
            'uid' => 'minimal-uid',
            'email' => 'minimal@example.com',
            'name' => null,
            'picture' => null,
        ];

        $user = $this->manager->createOrUpdateFromFirebase($claims);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('minimal@example.com', $user->email);
        $this->assertEquals('minimal-uid', $user->firebase_uid);
        $this->assertEquals('User', $user->name); // Default name
        $this->assertNull($user->avatar_url);
    }

    /**
     * Test updating user with new firebase_uid
     * 
     * @test
     */
    public function test_updates_user_firebase_uid()
    {
        $existingUser = User::factory()->create([
            'email' => 'updateuid@example.com',
            'firebase_uid' => 'old-firebase-uid',
        ]);

        $claims = [
            'uid' => 'new-firebase-uid',
            'email' => 'updateuid@example.com',
            'name' => 'Updated User',
            'picture' => null,
        ];

        $user = $this->manager->createOrUpdateFromFirebase($claims);

        $this->assertEquals('new-firebase-uid', $user->firebase_uid);
        $this->assertEquals($existingUser->id, $user->id);
    }

    /**
     * Test updating user avatar URL
     * 
     * @test
     */
    public function test_updates_user_avatar_url()
    {
        $existingUser = User::factory()->create([
            'email' => 'updateavatar@example.com',
            'avatar_url' => 'https://example.com/old-avatar.jpg',
        ]);

        $claims = [
            'uid' => 'firebase-uid-avatar',
            'email' => 'updateavatar@example.com',
            'name' => 'Avatar User',
            'picture' => 'https://example.com/new-avatar.jpg',
        ];

        $user = $this->manager->createOrUpdateFromFirebase($claims);

        $this->assertEquals('https://example.com/new-avatar.jpg', $user->avatar_url);
    }
}
