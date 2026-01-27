<?php

namespace App\Services;

use App\Models\User;

/**
 * Sanctum Token Manager
 * 
 * Manages Laravel Sanctum personal access tokens for API authentication.
 * Handles token generation, naming, abilities configuration, and revocation.
 */
class SanctumTokenManager
{
    /**
     * Generate Sanctum token for user
     * 
     * Creates a new personal access token for the given user with specified
     * name and abilities. The token can be used for API authentication.
     * 
     * @param User $user The user to generate token for
     * @param string $tokenName The name/identifier for the token
     * @return string Plain text token that should be returned to client
     */
    public function generateToken(User $user, string $tokenName = 'firebase-auth'): string
    {
        // Create a new personal access token with all abilities (*)
        // The createToken method returns a NewAccessToken object
        $tokenResult = $user->createToken($tokenName, ['*']);
        
        // Return the plain text token (this is what the client needs)
        return $tokenResult->plainTextToken;
    }
    
    /**
     * Revoke all tokens for user
     * 
     * Deletes all personal access tokens associated with the user.
     * Useful for logout functionality or security purposes.
     * 
     * @param User $user The user whose tokens should be revoked
     * @return void
     */
    public function revokeAllTokens(User $user): void
    {
        // Delete all tokens for this user
        $user->tokens()->delete();
    }
}
