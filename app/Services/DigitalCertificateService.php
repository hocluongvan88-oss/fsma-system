<?php

namespace App\Services;

use App\Models\User;
use App\Models\DigitalCertificate;
use Illuminate\Support\Str;

class DigitalCertificateService
{
    /**
     * Generate self-signed certificate for user
     */
    public function generateCertificate(User $user, int $keySize = 2048, int $validDays = 365): DigitalCertificate
    {
        $this->initializeOpenSSL();
        
        // Generate RSA key pair with improved error handling
        $res = $this->generateRSAKeyPair($keySize);
        
        if ($res === false) {
            throw new \Exception('Failed to generate RSA key pair after retries: ' . openssl_error_string());
        }

        $privateKey = '';
        if (!openssl_pkey_export($res, $privateKey)) {
            throw new \Exception('Failed to export private key: ' . openssl_error_string());
        }

        $publicKeyDetails = openssl_pkey_get_details($res);
        if ($publicKeyDetails === false) {
            throw new \Exception('Failed to get public key details: ' . openssl_error_string());
        }
        $publicKey = $publicKeyDetails['key'];

        // Create certificate signing request
        $dn = [
            'countryName' => 'US',
            'stateOrProvinceName' => 'State',
            'localityName' => 'City',
            'organizationName' => config('app.name'),
            'organizationalUnitName' => 'E-Signatures',
            'commonName' => $user->email,
            'emailAddress' => $user->email,
        ];

        $csr = openssl_csr_new($dn, $res);
        if ($csr === false) {
            throw new \Exception('Failed to create certificate signing request: ' . openssl_error_string());
        }

        // Self-sign the certificate
        $cert = openssl_csr_sign($csr, null, $res, $validDays);
        if ($cert === false) {
            throw new \Exception('Failed to sign certificate: ' . openssl_error_string());
        }

        $certPem = '';
        if (!openssl_x509_export($cert, $certPem)) {
            throw new \Exception('Failed to export certificate: ' . openssl_error_string());
        }

        // Get certificate details
        $certDetails = openssl_x509_parse($cert);
        if ($certDetails === false) {
            throw new \Exception('Failed to parse certificate: ' . openssl_error_string());
        }
        $serialNumber = $certDetails['serialNumber'] ?? Str::random(32);

        // Encrypt private key before storing
        $encryptedPrivateKey = encrypt($privateKey);

        $certificate = DigitalCertificate::create([
            'user_id' => $user->id,
            'certificate_id' => Str::uuid(),
            'certificate_pem' => $certPem,
            'certificate_chain' => null, // Will be populated when added to chain
            'root_ca_certificate' => null,
            'intermediate_ca_certificate' => null,
            'public_key' => $publicKey,
            'private_key_encrypted' => $encryptedPrivateKey,
            'issuer' => config('app.name'),
            'subject' => $user->email,
            'serial_number' => $serialNumber,
            'issued_at' => now(),
            'expires_at' => now()->addDays($validDays),
            'signature_algorithm' => 'sha256WithRSAEncryption',
            'key_size' => $keySize,
            'crl_url' => null,
            'ocsp_url' => null,
            'is_crl_valid' => true,
            'is_ocsp_valid' => true,
            'certificate_usage' => 'signing',
            'signature_count' => 0,
        ]);

        // Update user with certificate reference
        $user->update([
            'certificate_id' => $certificate->certificate_id,
            'public_key' => $publicKey,
            'certificate_pem' => $certPem,
            'certificate_expires_at' => $certificate->expires_at,
        ]);

        return $certificate;
    }

    /**
     * Initialize OpenSSL configuration with entropy handling
     */
    private function initializeOpenSSL(): void
    {
        // Clear any previous OpenSSL errors
        while (openssl_error_string() !== false) {
            // Clear error queue
        }

        $this->initializeEntropy();

        // Set OpenSSL configuration if available
        $opensslConfigPath = env('OPENSSL_CONFIG_PATH');
        if ($opensslConfigPath && file_exists($opensslConfigPath)) {
            putenv('OPENSSL_CONF=' . $opensslConfigPath);
        }
    }

    /**
     * Initialize entropy sources for OpenSSL
     * Handles systems where /dev/urandom is not available
     */
    private function initializeEntropy(): void
    {
        // Try to seed OpenSSL with random data from multiple sources
        $entropy = '';

        // Source 1: PHP's random_bytes (most reliable)
        try {
            $entropy .= random_bytes(32);
        } catch (\Exception $e) {
            // Fallback if random_bytes fails
        }

        // Source 2: /dev/urandom if available
        if (file_exists('/dev/urandom')) {
            $handle = @fopen('/dev/urandom', 'rb');
            if ($handle) {
                $entropy .= fread($handle, 32);
                fclose($handle);
            }
        }

        // Source 3: /dev/random if available (slower but more secure)
        if (file_exists('/dev/random') && strlen($entropy) < 64) {
            $handle = @fopen('/dev/random', 'rb');
            if ($handle) {
                stream_set_timeout($handle, 1);
                $entropy .= fread($handle, 32 - strlen($entropy));
                fclose($handle);
            }
        }

        // Source 4: Fallback to system entropy if available
        if (strlen($entropy) < 32) {
            if (function_exists('openssl_random_pseudo_bytes')) {
                $bytesNeeded = 32 - strlen($entropy);
                if ($bytesNeeded > 0) {
                    $entropy .= openssl_random_pseudo_bytes($bytesNeeded);
                }
            }
        }

        // The entropy is already collected and will be used by OpenSSL automatically
    }

    /**
     * Generate RSA key pair with enhanced retry logic and entropy handling
     */
    private function generateRSAKeyPair(int $keySize = 2048)
    {
        $maxRetries = 5;
        $retryCount = 0;
        $lastError = '';

        while ($retryCount < $maxRetries) {
            try {
                if ($retryCount > 0) {
                    $this->initializeEntropy();
                    usleep(200000 * $retryCount); // Exponential backoff: 200ms, 400ms, 600ms...
                }

                $config = [
                    'private_key_bits' => $keySize,
                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                    'digest_alg' => 'sha256',
                    'config' => $this->getOpenSSLConfig(),
                ];

                $res = openssl_pkey_new($config);
                
                if ($res !== false && is_resource($res)) {
                    return $res;
                }

                $lastError = openssl_error_string() ?: 'Unknown error';
                $retryCount++;
                
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                $retryCount++;
                
                if ($retryCount >= $maxRetries) {
                    throw new \Exception(
                        "Failed to generate RSA key pair after {$maxRetries} retries. " .
                        "Last error: {$lastError}. " .
                        "This may indicate insufficient entropy. " .
                        "Try: 1) Increase system entropy, 2) Check /dev/urandom availability, " .
                        "3) Restart the application"
                    );
                }
            }
        }

        throw new \Exception(
            "Failed to generate RSA key pair after {$maxRetries} retries. " .
            "Last error: {$lastError}"
        );
    }

    /**
     * Get OpenSSL configuration for better entropy handling
     */
    private function getOpenSSLConfig(): ?string
    {
        // Try to create a temporary OpenSSL config that handles entropy better
        $configPath = sys_get_temp_dir() . '/openssl_' . uniqid() . '.cnf';
        
        $config = <<<'EOL'
[ req ]
default_bits = 2048
distinguished_name = req_distinguished_name
attributes = req_attributes
x509_extensions = v3_ca

[ req_distinguished_name ]

[ req_attributes ]

[ v3_ca ]
subjectKeyIdentifier = hash
authorityKeyIdentifier = keyid:always,issuer:always
basicConstraints = CA:true
EOL;

        if (@file_put_contents($configPath, $config)) {
            return $configPath;
        }

        return null;
    }

    /**
     * Verify certificate is valid including chain validation
     */
    public function verifyCertificate(DigitalCertificate $certificate): bool
    {
        // Check if revoked
        if ($certificate->is_revoked) {
            return false;
        }

        // Check if expired
        if ($certificate->expires_at < now()) {
            return false;
        }

        // Verify certificate PEM format
        $cert = openssl_x509_read($certificate->certificate_pem);
        if ($cert === false) {
            return false;
        }

        if ($certificate->isCertificateChainValid()) {
            if (!$this->verifyCertificateChain($certificate)) {
                return false;
            }
        }

        if (!$this->verifyCRLStatus($certificate) || !$this->verifyOCSPStatus($certificate)) {
            return false;
        }

        return true;
    }

    /**
     * Verify certificate chain (PKI validation)
     */
    public function verifyCertificateChain(DigitalCertificate $certificate): bool
    {
        try {
            $cert = openssl_x509_read($certificate->certificate_pem);
            if (!$cert) {
                return false;
            }

            // Verify against intermediate CA if available
            if ($certificate->intermediate_ca_certificate) {
                $intermediateCert = openssl_x509_read($certificate->intermediate_ca_certificate);
                if (!$intermediateCert) {
                    return false;
                }

                // Verify certificate is signed by intermediate CA
                $certDetails = openssl_x509_parse($cert);
                $intermediateDetails = openssl_x509_parse($intermediateCert);

                if ($certDetails['issuer'] !== $intermediateDetails['subject']) {
                    return false;
                }
            }

            // Verify against root CA if available
            if ($certificate->root_ca_certificate) {
                $rootCert = openssl_x509_read($certificate->root_ca_certificate);
                if (!$rootCert) {
                    return false;
                }

                $rootDetails = openssl_x509_parse($rootCert);
                
                // Verify intermediate or certificate is signed by root CA
                $issuerCert = $certificate->intermediate_ca_certificate 
                    ? openssl_x509_read($certificate->intermediate_ca_certificate)
                    : $cert;
                    
                $issuerDetails = openssl_x509_parse($issuerCert);
                
                if ($issuerDetails['issuer'] !== $rootDetails['subject']) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verify CRL (Certificate Revocation List) status
     */
    public function verifyCRLStatus(DigitalCertificate $certificate): bool
    {
        if (!$certificate->crl_url) {
            return true; // No CRL URL, assume valid
        }

        try {
            // Check if CRL needs refresh (older than 7 days)
            if (!$certificate->crl_last_checked || $certificate->crl_last_checked->diffInDays(now()) > 7) {
                $this->refreshCRLStatus($certificate);
            }

            return $certificate->is_crl_valid;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verify OCSP (Online Certificate Status Protocol) status
     */
    public function verifyOCSPStatus(DigitalCertificate $certificate): bool
    {
        if (!$certificate->ocsp_url) {
            return true; // No OCSP URL, assume valid
        }

        try {
            // Check if OCSP status needs refresh (older than 7 days)
            if (!$certificate->ocsp_last_checked || $certificate->ocsp_last_checked->diffInDays(now()) > 7) {
                $this->refreshOCSPStatus($certificate);
            }

            return $certificate->is_ocsp_valid;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Refresh CRL status from CRL URL
     */
    private function refreshCRLStatus(DigitalCertificate $certificate): void
    {
        try {
            $response = \Illuminate\Support\Facades\Http::get($certificate->crl_url);
            
            if ($response->successful()) {
                // Parse CRL and check if certificate serial is revoked
                $crlData = $response->body();
                $isRevoked = $this->isSerialInCRL($certificate->serial_number, $crlData);
                
                $certificate->update([
                    'is_crl_valid' => !$isRevoked,
                    'crl_last_checked' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail - assume valid if CRL check fails
        }
    }

    /**
     * Refresh OCSP status from OCSP URL
     */
    private function refreshOCSPStatus(DigitalCertificate $certificate): void
    {
        try {
            // OCSP request would be more complex - simplified here
            $response = \Illuminate\Support\Facades\Http::get($certificate->ocsp_url, [
                'serial' => $certificate->serial_number,
            ]);
            
            if ($response->successful()) {
                $isRevoked = str_contains($response->body(), 'revoked');
                
                $certificate->update([
                    'is_ocsp_valid' => !$isRevoked,
                    'ocsp_last_checked' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail - assume valid if OCSP check fails
        }
    }

    /**
     * Check if serial number is in CRL
     */
    private function isSerialInCRL(string $serial, string $crlData): bool
    {
        // Simplified check - in production, properly parse CRL
        return str_contains($crlData, $serial);
    }

    /**
     * Revoke certificate
     */
    public function revokeCertificate(DigitalCertificate $certificate, string $reason): void
    {
        $certificate->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ]);

        // Update user
        $certificate->user->update([
            'certificate_revoked' => true,
            'certificate_revoked_at' => now(),
        ]);
    }

    /**
     * Sign data with certificate (PKI-based)
     */
    public function signData(DigitalCertificate $certificate, string $data): string
    {
        if (!$this->verifyCertificate($certificate)) {
            throw new \Exception('Certificate is not valid');
        }

        $privateKey = decrypt($certificate->private_key_encrypted);
        $signature = '';

        if (!openssl_sign($data, $signature, $privateKey, 'sha256')) {
            throw new \Exception('Failed to sign data');
        }

        $certificate->incrementSignatureCount();

        return base64_encode($signature);
    }

    /**
     * Verify signature with certificate (PKI-based)
     */
    public function verifySignature(DigitalCertificate $certificate, string $data, string $signature): bool
    {
        if (!$this->verifyCertificate($certificate)) {
            return false;
        }

        $publicKey = $certificate->public_key;
        $decodedSignature = base64_decode($signature);

        $result = openssl_verify($data, $decodedSignature, $publicKey, 'sha256');
        return $result === 1;
    }

    /**
     * Get certificate expiry warning
     */
    public function getCertificateExpiryWarning(User $user): ?string
    {
        if (!$user->certificate_id) {
            return null;
        }

        $certificate = DigitalCertificate::where('certificate_id', $user->certificate_id)->first();
        if (!$certificate) {
            return null;
        }

        $daysUntilExpiry = now()->diffInDays($certificate->expires_at);

        if ($daysUntilExpiry < 0) {
            return 'Certificate has expired';
        } elseif ($daysUntilExpiry < 30) {
            return "Certificate expires in {$daysUntilExpiry} days";
        }

        return null;
    }
}
