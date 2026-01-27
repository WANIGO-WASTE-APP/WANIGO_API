<?php

namespace Tests\Unit;

use App\Http\Requests\FirebaseAuthRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Unit tests for FirebaseAuthRequest validation.
 * 
 * Tests validation rules for Firebase authentication endpoint:
 * - id_token field is required
 * - id_token must be a string
 * 
 * Validates: Requirement 1.1
 */
class FirebaseAuthRequestTest extends TestCase
{
    /**
     * Test that validation passes with valid id_token
     *
     * @test
     */
    public function test_validation_passes_with_valid_id_token()
    {
        $request = new FirebaseAuthRequest();
        $validator = Validator::make(
            ['id_token' => 'valid.firebase.token'],
            $request->rules()
        );

        $this->assertFalse(
            $validator->fails(),
            'Validation should pass when id_token is a valid string'
        );
    }

    /**
     * Test that validation fails when id_token is missing
     *
     * @test
     */
    public function test_validation_fails_when_id_token_is_missing()
    {
        $request = new FirebaseAuthRequest();
        $validator = Validator::make(
            [],
            $request->rules()
        );

        $this->assertTrue(
            $validator->fails(),
            'Validation should fail when id_token is missing'
        );

        $this->assertArrayHasKey(
            'id_token',
            $validator->errors()->toArray(),
            'Error should be for id_token field'
        );
    }

    /**
     * Test that validation fails when id_token is null
     *
     * @test
     */
    public function test_validation_fails_when_id_token_is_null()
    {
        $request = new FirebaseAuthRequest();
        $validator = Validator::make(
            ['id_token' => null],
            $request->rules()
        );

        $this->assertTrue(
            $validator->fails(),
            'Validation should fail when id_token is null'
        );
    }

    /**
     * Test that validation fails when id_token is not a string
     *
     * @test
     */
    public function test_validation_fails_when_id_token_is_not_string()
    {
        $request = new FirebaseAuthRequest();
        
        // Test with integer
        $validator = Validator::make(
            ['id_token' => 12345],
            $request->rules()
        );

        $this->assertTrue(
            $validator->fails(),
            'Validation should fail when id_token is an integer'
        );

        // Test with array
        $validator = Validator::make(
            ['id_token' => ['token' => 'value']],
            $request->rules()
        );

        $this->assertTrue(
            $validator->fails(),
            'Validation should fail when id_token is an array'
        );

        // Test with boolean
        $validator = Validator::make(
            ['id_token' => true],
            $request->rules()
        );

        $this->assertTrue(
            $validator->fails(),
            'Validation should fail when id_token is a boolean'
        );
    }

    /**
     * Test that validation fails with empty string
     *
     * @test
     */
    public function test_validation_fails_with_empty_string()
    {
        $request = new FirebaseAuthRequest();
        $validator = Validator::make(
            ['id_token' => ''],
            $request->rules()
        );

        // Empty string should fail the 'required' rule in Laravel
        $this->assertTrue(
            $validator->fails(),
            'Validation should fail with empty string'
        );
    }

    /**
     * Test that validation passes with very long string (JWT tokens can be long)
     *
     * @test
     */
    public function test_validation_passes_with_long_token()
    {
        $request = new FirebaseAuthRequest();
        $longToken = str_repeat('a', 2000); // Simulate long JWT token
        
        $validator = Validator::make(
            ['id_token' => $longToken],
            $request->rules()
        );

        $this->assertFalse(
            $validator->fails(),
            'Validation should pass with long token strings (JWT tokens can be long)'
        );
    }

    /**
     * Test that authorize method returns true (public endpoint)
     *
     * @test
     */
    public function test_authorize_returns_true()
    {
        $request = new FirebaseAuthRequest();
        
        $this->assertTrue(
            $request->authorize(),
            'Firebase authentication should be publicly accessible'
        );
    }

    /**
     * Test custom error messages are defined
     *
     * @test
     */
    public function test_custom_error_messages_are_defined()
    {
        $request = new FirebaseAuthRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey(
            'id_token.required',
            $messages,
            'Custom message for required rule should be defined'
        );

        $this->assertArrayHasKey(
            'id_token.string',
            $messages,
            'Custom message for string rule should be defined'
        );

        $this->assertStringContainsString(
            'required',
            $messages['id_token.required'],
            'Required message should mention "required"'
        );
    }

    /**
     * Test custom attributes are defined
     *
     * @test
     */
    public function test_custom_attributes_are_defined()
    {
        $request = new FirebaseAuthRequest();
        $attributes = $request->attributes();

        $this->assertArrayHasKey(
            'id_token',
            $attributes,
            'Custom attribute for id_token should be defined'
        );

        $this->assertStringContainsString(
            'token',
            strtolower($attributes['id_token']),
            'Attribute should describe the token field'
        );
    }
}
