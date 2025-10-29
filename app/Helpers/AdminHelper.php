<?php

namespace App\Helpers;

use App\Models\User;

class AdminHelper
{
    /**
     * Admin email - centralized configuration
     * Renamed from SYSTEM_ADMIN_EMAIL to ADMIN_EMAIL
     */
    const ADMIN_EMAIL = 'admin@fsma204.com';
    
    /**
     * Check if user is admin
     * Renamed from isSystemAdmin to isAdmin and simplified logic
     * 
     * @param User|null $user
     * @return bool
     */
    public static function isAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        return $user->isAdmin();
    }
    
    /**
     * Check if user can access admin functions
     * 
     * @param User|null $user
     * @return bool
     */
    public static function canAccessAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        return $user->isAdmin();
    }
    
    /**
     * Get admin email
     * Renamed from getSystemAdminEmail to getAdminEmail
     * 
     * @return string
     */
    public static function getAdminEmail(): string
    {
        return self::ADMIN_EMAIL;
    }
    
    /**
     * Check if email is admin
     * Renamed from isSystemAdminEmail to isAdminEmail
     * 
     * @param string $email
     * @return bool
     */
    public static function isAdminEmail(string $email): bool
    {
        return $email === self::ADMIN_EMAIL;
    }
}
