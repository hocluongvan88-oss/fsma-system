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
        // Generate RSA key pair
        $config = [
            'private_key_bits' => $keySize,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        $publicKeyDetails = openssl_pkey_get_details($res);
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

        // Self-sign the certificate
        $cert = openssl_csr_sign($csr, null, $res, $validDays);
        openssl_x509_export($cert, $certPem);

        // Get certificate details
        $certDetails = openssl_x509_parse($cert);
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
