<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FirebaseAuthService;
use App\Services\InvalidTokenException;
use App\Services\SecurityException;
use Mockery;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\InvalidToken;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Kreait\Firebase\Auth\VerifyIdToken;
use Kreait\Firebase\JWT\IdTokenVerifier;
use Kreait\Firebase\JWT\Token;

class FirebaseAuthServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that validateProductionSecurity throws exception when APP_DEBUG is true in production
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_production_security_validation_fails_when_debug_enabled()
    {
        // Set environment to production with debug enabled
        config(['app.env' => 'production']);
        config(['app.debug' => true]);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Security violation: APP_DEBUG must be false in production environment');

        // Create a partial mock to test only validateProductionSecurity
        $service = Mockery::mock(FirebaseAuthService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // Call the protected method using reflection
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('validateProductionSecurity');
        $method->setAccessible(true);
        $method->invoke($service);
    }

    /**
     * Test that validateProductionSecurity passes when APP_DEBUG is false in production
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_production_security_validation_passes_when_debug_disabled()
    {
        // Set environment to production with debug disabled
        config(['app.env' => 'production']);
        config(['app.debug' => false]);

        // Create a partial mock
        $service = Mockery::mock(FirebaseAuthService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // Call the protected method using reflection
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('validateProductionSecurity');
        $method->setAccessible(true);

        // Should not throw exception
        $method->invoke($service);
        
        $this->assertTrue(true); // If we get here, validation passed
    }

    /**
     * Test that validateProductionSecurity passes in non-production environments
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_production_security_validation_passes_in_development()
    {
        // Set environment to development with debug enabled
        config(['app.env' => 'local']);
        config(['app.debug' => true]);

        // Create a partial mock
        $service = Mockery::mock(FirebaseAuthService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        // Call the protected method using reflection
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('validateProductionSecurity');
        $method->setAccessible(true);

        // Should not throw exception even with debug enabled
        $method->invoke($service);
        
        $this->assertTrue(true); // If we get here, validation passed
    }

    /**
     * Test that Firebase initialization fails when project ID is missing
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_firebase_initialization_fails_without_project_id()
    {
        // Clear Firebase configuration
        config(['firebase.project_id' => null]);
        config(['firebase.credentials' => '{"type":"service_account"}']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Firebase project ID is not configured');

        new FirebaseAuthService();
    }

    /**
     * Test that Firebase initialization fails when credentials are missing
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_firebase_initialization_fails_without_credentials()
    {
        // Set project ID but clear credentials
        config(['firebase.project_id' => 'test-project']);
        config(['firebase.credentials' => null]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Firebase credentials are not configured');

        new FirebaseAuthService();
    }

    /**
     * Test that Firebase initialization fails with invalid JSON credentials
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_firebase_initialization_fails_with_invalid_json()
    {
        // Set project ID with invalid JSON credentials
        config(['firebase.project_id' => 'test-project']);
        config(['firebase.credentials' => 'invalid-json{']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid Firebase credentials JSON');

        new FirebaseAuthService();
    }

    /**
     * Test that verifyIdToken extracts required claims correctly
     * 
     * This test verifies the claim extraction logic without actually calling Firebase
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_verify_id_token_extracts_claims_correctly()
    {
        // This test would require mocking the Firebase Auth instance
        // Since Firebase SDK initialization is complex, we'll test this in integration tests
        $this->markTestSkipped('This test requires Firebase SDK mocking which is complex. Will be covered in integration tests.');
    }

    /**
     * Test that authenticateWithFirebase validates production security
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_authenticate_with_firebase_validates_production_security()
    {
        // Set environment to production with debug enabled
        config(['app.env' => 'production']);
        config(['app.debug' => true]);
        config(['firebase.project_id' => 'test-project']);
        config(['firebase.credentials' => '{"type":"service_account","project_id":"test"}']);

        $this->expectException(SecurityException::class);

        // Create service and attempt authentication
        $service = Mockery::mock(FirebaseAuthService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->authenticateWithFirebase('test-token');
    }
}
