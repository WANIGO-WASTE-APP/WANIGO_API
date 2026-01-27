<?php

namespace App\Traits;

/**
 * Trait ResolvesContactInfo
 * 
 * Provides contact information resolution logic for resources.
 * Implements priority-based phone number resolution.
 * 
 * Requirements: 2.2
 */
trait ResolvesContactInfo
{
    /**
     * Resolve phone number with priority logic.
     * 
     * Priority order:
     * 1. nomor_telepon_publik (public phone number)
     * 2. nomor_telepon (private phone number)
     * 3. null (if both are unavailable)
     * 
     * @return string|null The resolved phone number or null
     */
    protected function resolvePhone(): ?string
    {
        // Check if nomor_telepon_publik exists and is not null
        if (isset($this->nomor_telepon_publik) && $this->nomor_telepon_publik !== null) {
            return $this->nomor_telepon_publik;
        }
        
        // Fallback to nomor_telepon if it exists
        if (isset($this->nomor_telepon) && $this->nomor_telepon !== null) {
            return $this->nomor_telepon;
        }
        
        // Return null if neither field is available
        return null;
    }
}
