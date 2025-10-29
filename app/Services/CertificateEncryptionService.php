<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class CertificateEncryptionService
{
    /**
     * Encrypt private key with user password
     * 
     * @param string $privateKey
     * @param string $userPassword
     * @return string
     */
    public function encryptPrivateKey(string $privateKey, string $userPassword): string
    {
        $key = hash('sha256', $userPassword, true);
        $iv = openssl_random_pseudo_bytes(16);
        
        $encrypted = openssl_encrypt(
            $privateKey,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        if ($encrypted === false) {
            throw new \Exception('Failed to encrypt private key');
        }
        
        // Combine IV and encrypted data
        $combined = base64_encode($iv . $encrypted);
        
        // Additional Laravel encryption layer
        return Crypt::encryptString($combined);
    }
    
    /**
     * Decrypt private key with user password
     * 
     * @param string $encryptedPrivateKey
     * @param string $userPassword
     * @return string
     */
    public function decryptPrivateKey(string $encryptedPrivateKey, string $userPassword): string
    {
        try {
            // Remove Laravel encryption layer
            $combined = Crypt::decryptString($encryptedPrivateKey);
            $decoded = base64_decode($combined);
            
            // Extract IV and encrypted data
            $iv = substr($decoded, 0, 16);
            $encrypted = substr($decoded, 16);
            
            // Decrypt with user password
            $key = hash('sha256', $userPassword, true);
            
            $decrypted = openssl_decrypt(
                $encrypted,
                'AES-256-CBC',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );
            
            if ($decrypted === false) {
                throw new \Exception('Failed to decrypt private key - invalid password');
            }
            
            return $decrypted;
            
        } catch (\Exception $e) {
            Log::error('Private key decryption failed', [
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Invalid password or corrupted private key');
        }
    }
    
    /**
     * Verify if private key can be decrypted with password
     * 
     * @param string $encryptedPrivateKey
     * @param string $userPassword
     * @return bool
     */
    public function verifyPassword(string $encryptedPrivateKey, string $userPassword): bool
    {
        try {
            $this->decryptPrivateKey($encryptedPrivateKey, $userPassword);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
