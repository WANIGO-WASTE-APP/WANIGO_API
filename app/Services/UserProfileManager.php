<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class UserProfileManager
{
    /**
     * Create or update user from Firebase claims
     * 
     * @param array $claims [uid, email, name, picture]
     * @return User
     * @throws Exception if user creation/update fails
     */
    public function createOrUpdateFromFirebase(array $claims): User
    {
        try {
            DB::beginTransaction();

            // Find existing user by email
            $user = $this->findByEmail($claims['email']);

            if ($user) {
                // Update existing user
                $user = $this->updateUser($user, $claims);
            } else {
                // Create new user
                $user = $this->createUser($claims);
            }

            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to create or update user profile: ' . $e->getMessage());
        }
    }

    /**
     * Find user by email
     * 
     * @param string $email
     * @return User|null
     */
    protected function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create new user from Firebase data
     * 
     * @param array $claims [uid, email, name, picture]
     * @return User
     * @throws Exception if user creation fails
     */
    protected function createUser(array $claims): User
    {
        try {
            $user = User::create([
                'name' => $claims['name'] ?? 'User',
                'email' => $claims['email'],
                'firebase_uid' => $claims['uid'],
                'avatar_url' => $claims['picture'] ?? null,
                'password' => bcrypt(uniqid()), // Generate random password for Firebase users
                'role' => 'nasabah', // Default role for new Firebase users
            ]);

            return $user;
        } catch (Exception $e) {
            throw new Exception('Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Update existing user with Firebase data
     * 
     * @param User $user
     * @param array $claims [uid, email, name, picture]
     * @return User
     * @throws Exception if user update fails
     */
    protected function updateUser(User $user, array $claims): User
    {
        try {
            $user->update([
                'name' => $claims['name'] ?? $user->name,
                'firebase_uid' => $claims['uid'],
                'avatar_url' => $claims['picture'] ?? $user->avatar_url,
            ]);

            return $user->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to update user: ' . $e->getMessage());
        }
    }
}
