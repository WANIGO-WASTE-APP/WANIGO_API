<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\InvalidToken;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;
use Exception;

class FirebaseAuthService
{
    protected FirebaseAuth $auth;

    /**
     * Initialize Firebase Auth Service
     * 
     * @throws Exception if Firebase initialization fails
     */
    public function __construct()
    {
        $this->initializeFirebase();
    }

    /**
     * Initialize Firebase SDK with credentials from config
     * 
     * @throws Exception if configuration is missing or invalid
     */
    protected function initializeFirebase(): void
    {
        $projectId = config('firebase.project_id');
        $credentialsPath = config('firebase.credentials_path');

        if (empty($projectId)) {
            throw new Exception('Firebase project ID is not configured. Please set FIREBASE_PROJECT_ID in your .env file.');
        }

        if (empty($credentialsPath)) {
            throw new Exception('Firebase credentials path is not configured. Please set FIREBASE_CREDENTIALS in your .env file.');
        }

        // Build full path
        $fullPath = base_path($credentialsPath);

        if (!file_exists($fullPath)) {
            throw new Exception("Firebase credentials file not found at: {$fullPath}");
        }

        try {
            // Read and validate JSON file
            $credentialsJson = file_get_contents($fullPath);
            $credentials = json_decode($credentialsJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid Firebase credentials JSON: ' . json_last_error_msg());
            }

            // Initialize Firebase with service account file
            $factory = (new Factory)
                ->withServiceAccount($fullPath)
                ->withProjectId($projectId);

            $this->auth = $factory->createAuth();
        } catch (Exception $e) {
            throw new Exception('Failed to initialize Firebase: ' . $e->getMessage());
        }
    }

    /**
     * Authenticate user with Firebase ID token
     * 
     * @param string $idToken Firebase ID token from client
     * @return array User claims [uid, email, name, picture]
     * @throws InvalidTokenException if token is invalid/expired
     * @throws SecurityException if production constraints violated
     */
    public function authenticateWithFirebase(string $idToken): array
    {
        // Validate production security constraints
        $this->validateProductionSecurity();

        // Verify the Firebase ID token
        $claims = $this->verifyIdToken($idToken);

        return $claims;
    }

    /**
     * Verify Firebase ID token and extract user claims
     * 
     * @param string $idToken Firebase ID token
     * @return array User claims [uid, email, name, picture]
     * @throws InvalidTokenException if token is invalid/expired
     */
    protected function verifyIdToken(string $idToken): array
    {
        try {
            // Verify the ID token with Firebase
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);

            // Extract claims from the verified token
            $claims = $verifiedIdToken->claims()->all();

            // Extract required user information
            $uid = $claims['sub'] ?? $claims['user_id'] ?? null;
            $email = $claims['email'] ?? null;
            $name = $claims['name'] ?? null;
            $picture = $claims['picture'] ?? null;

            if (empty($uid) || empty($email)) {
                throw new InvalidTokenException('Token does not contain required user information (uid, email)');
            }

            return [
                'uid' => $uid,
                'email' => $email,
                'name' => $name,
                'picture' => $picture,
            ];
        } catch (InvalidToken $e) {
            throw new InvalidTokenException('Invalid Firebase token: ' . $e->getMessage());
        } catch (RevokedIdToken $e) {
            throw new InvalidTokenException('Firebase token has been revoked: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new InvalidTokenException('Failed to verify Firebase token: ' . $e->getMessage());
        }
    }

    /**
     * Validate production security constraints
     * 
     * @throws SecurityException if APP_DEBUG is true in production
     */
    protected function validateProductionSecurity(): void
    {
        $environment = config('app.env');
        $debug = config('app.debug');

        if ($environment === 'production' && $debug === true) {
            throw new SecurityException('Security violation: APP_DEBUG must be false in production environment');
        }
    }
}

/**
 * Custom exception for invalid Firebase tokens
 */
class InvalidTokenException extends Exception
{
    //
}

/**
 * Custom exception for security violations
 */
class SecurityException extends Exception
{
    //
}
