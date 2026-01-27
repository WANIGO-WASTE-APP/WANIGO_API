<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\FirebaseAuthRequest;
use App\Services\FirebaseAuthService;
use App\Services\UserProfileManager;
use App\Services\SanctumTokenManager;
use App\Services\InvalidTokenException;
use App\Services\SecurityException;
use Illuminate\Http\JsonResponse;
use Exception;

/**
 * Firebase Authentication Controller
 * 
 * Handles Firebase-based Google Sign-In authentication.
 * Coordinates FirebaseAuthService, UserProfileManager, and SanctumTokenManager
 * to verify Firebase ID tokens and issue Sanctum API tokens.
 * 
 * Requirements: 1.6, 5.1, 5.2, 5.3
 */
class FirebaseAuthController extends Controller
{
    protected FirebaseAuthService $firebaseAuthService;
    protected UserProfileManager $userProfileManager;
    protected SanctumTokenManager $sanctumTokenManager;

    /**
     * Constructor - inject required services
     */
    public function __construct(
        FirebaseAuthService $firebaseAuthService,
        UserProfileManager $userProfileManager,
        SanctumTokenManager $sanctumTokenManager
    ) {
        $this->firebaseAuthService = $firebaseAuthService;
        $this->userProfileManager = $userProfileManager;
        $this->sanctumTokenManager = $sanctumTokenManager;
    }

    /**
     * Authenticate with Firebase Google Sign-In
     * 
     * This endpoint accepts a Firebase ID token from the client, verifies it,
     * creates or updates the user profile, and returns a Sanctum API token.
     * 
     * @param FirebaseAuthRequest $request Validated request containing id_token
     * @return JsonResponse
     * 
     * Success Response (200):
     * {
     *   "success": true,
     *   "message": "Login success",
     *   "data": {
     *     "token": "<sanctum_token>",
     *     "token_type": "Bearer",
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "avatar_url": "https://..."
     *     }
     *   }
     * }
     * 
     * Error Response (401):
     * {
     *   "success": false,
     *   "message": "Invalid or expired Firebase token"
     * }
     * 
     * Error Response (500):
     * {
     *   "success": false,
     *   "message": "Security configuration error"
     * }
     * 
     * Requirements: 1.6, 5.1, 5.2, 5.3
     */
    public function googleSignIn(FirebaseAuthRequest $request): JsonResponse
    {
        try {
            // Step 1: Verify Firebase ID token and extract user claims
            // This validates the token with Firebase and returns user data
            $claims = $this->firebaseAuthService->authenticateWithFirebase(
                $request->input('id_token')
            );

            // Step 2: Create or update user profile from Firebase claims
            // This handles both new user creation and existing user updates
            $user = $this->userProfileManager->createOrUpdateFromFirebase($claims);

            // Step 3: Generate Sanctum API token for the user
            // This token will be used for subsequent API requests
            $token = $this->sanctumTokenManager->generateToken($user, 'firebase-auth');

            // Step 4: Format and return success response
            // Response structure follows Requirement 5.1, 5.2
            return response()->json([
                'success' => true,
                'message' => 'Login success',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar_url' => $user->avatar_url,
                    ],
                ],
            ], 200);

        } catch (InvalidTokenException $e) {
            // Handle invalid, expired, or malformed Firebase tokens
            // Requirement 1.7: Return 401 for invalid tokens
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired Firebase token',
            ], 401);

        } catch (SecurityException $e) {
            // Handle production security violations (APP_DEBUG=true in production)
            // Requirement 1.8, 6.3: Return 500 for security configuration errors
            return response()->json([
                'success' => false,
                'message' => 'Security configuration error',
            ], 500);

        } catch (Exception $e) {
            // Handle any other unexpected errors
            // Log the error for debugging but don't expose details to client
            \Log::error('Firebase authentication error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            // Requirement 5.4: Return error response with success: false
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed. Please try again.',
            ], 500);
        }
    }
}
