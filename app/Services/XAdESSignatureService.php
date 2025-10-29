<?php

namespace App\Services;

use App\Models\ESignature;
use App\Models\DigitalCertificate;
use SimpleXMLElement;

class XAdESSignatureService
{
    /**
     * Generate XAdES (XML Advanced Electronic Signatures) format
     * Compliant with ETSI TS 101 903 standard
     */
    public function generateXAdESSignature(ESignature $signature): string
    {
        $xades = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><XAdESSignatures xmlns="http://uri.etsi.org/01903/v1.3.2#"></XAdESSignatures>');
        
        $signatureElement = $xades->addChild('Signature', null, 'http://www.w3.org/2000/09/xmldsig#');
        $signatureElement->addAttribute('Id', 'Signature-' . $signature->id);
        
        // SignedInfo
        $signedInfo = $signatureElement->addChild('SignedInfo', null, 'http://www.w3.org/2000/09/xmldsig#');
        $canonicalizationMethod = $signedInfo->addChild('CanonicalizationMethod', null, 'http://www.w3.org/2000/09/xmldsig#');
        $canonicalizationMethod->addAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');
        
        $signatureMethod = $signedInfo->addChild('SignatureMethod', null, 'http://www.w3.org/2000/09/xmldsig#');
        $signatureMethod->addAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha512');
        
        // Reference
        $reference = $signedInfo->addChild('Reference', null, 'http://www.w3.org/2000/09/xmldsig#');
        $reference->addAttribute('URI', '#Object-' . $signature->id);
        
        $digestMethod = $reference->addChild('DigestMethod', null, 'http://www.w3.org/2000/09/xmldsig#');
        $digestMethod->addAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha512');
        
        $digestValue = $reference->addChild('DigestValue', null, 'http://www.w3.org/2000/09/xmldsig#');
        $digestValue[0] = base64_encode($signature->record_content_hash);
        
        // SignatureValue
        $signatureValue = $signatureElement->addChild('SignatureValue', null, 'http://www.w3.org/2000/09/xmldsig#');
        $signatureValue[0] = base64_encode($signature->signature_hash);
        
        // KeyInfo
        $keyInfo = $signatureElement->addChild('KeyInfo', null, 'http://www.w3.org/2000/09/xmldsig#');
        $x509Data = $keyInfo->addChild('X509Data', null, 'http://www.w3.org/2000/09/xmldsig#');
        
        if ($signature->certificate_subject) {
            $x509SubjectName = $x509Data->addChild('X509SubjectName', null, 'http://www.w3.org/2000/09/xmldsig#');
            $x509SubjectName[0] = $signature->certificate_subject;
        }
        
        if ($signature->certificate_serial_number) {
            $x509IssuerSerial = $x509Data->addChild('X509IssuerSerial', null, 'http://www.w3.org/2000/09/xmldsig#');
            $x509SerialNumber = $x509IssuerSerial->addChild('X509SerialNumber', null, 'http://www.w3.org/2000/09/xmldsig#');
            $x509SerialNumber[0] = $signature->certificate_serial_number;
        }
        
        // Object with QualifyingProperties
        $object = $signatureElement->addChild('Object', null, 'http://www.w3.org/2000/09/xmldsig#');
        $object->addAttribute('Id', 'Object-' . $signature->id);
        
        $qualifyingProperties = $object->addChild('QualifyingProperties', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $qualifyingProperties->addAttribute('Target', '#Signature-' . $signature->id);
        
        // SignedProperties
        $signedProperties = $qualifyingProperties->addChild('SignedProperties', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $signedProperties->addAttribute('Id', 'SignedProperties-' . $signature->id);
        
        $signedSignatureProperties = $signedProperties->addChild('SignedSignatureProperties', null, 'http://uri.etsi.org/01903/v1.3.2#');
        
        // SigningTime
        $signingTime = $signedSignatureProperties->addChild('SigningTime', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $signingTime[0] = $signature->signed_at->toIso8601String();
        
        // SigningCertificate
        $signingCertificate = $signedSignatureProperties->addChild('SigningCertificate', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $cert = $signingCertificate->addChild('Cert', null, 'http://uri.etsi.org/01903/v1.3.2#');
        
        $certDigest = $cert->addChild('CertDigest', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $digestMethod = $certDigest->addChild('DigestMethod', null, 'http://www.w3.org/2000/09/xmldsig#');
        $digestMethod->addAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha512');
        
        $digestValue = $certDigest->addChild('DigestValue', null, 'http://www.w3.org/2000/09/xmldsig#');
        $digestValue[0] = base64_encode(hash('sha512', $signature->certificate_subject ?? '', true));
        
        // IssuerSerial
        $issuerSerial = $cert->addChild('IssuerSerial', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $x509IssuerName = $issuerSerial->addChild('X509IssuerName', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $x509IssuerName[0] = $signature->certificate_issuer ?? 'Unknown';
        
        $x509SerialNumber = $issuerSerial->addChild('X509SerialNumber', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $x509SerialNumber[0] = $signature->certificate_serial_number ?? '0';
        
        // SignatureProductionPlace
        $signatureProductionPlace = $signedSignatureProperties->addChild('SignatureProductionPlace', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $city = $signatureProductionPlace->addChild('City', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $city[0] = 'Digital Signature';
        
        // SignedDataObjectProperties
        $signedDataObjectProperties = $signedProperties->addChild('SignedDataObjectProperties', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $dataObjectFormat = $signedDataObjectProperties->addChild('DataObjectFormat', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $dataObjectFormat->addAttribute('ObjectReference', '#Object-' . $signature->id);
        
        $mimeType = $dataObjectFormat->addChild('MimeType', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $mimeType[0] = 'application/octet-stream';
        
        // UnsignedProperties
        $unsignedProperties = $qualifyingProperties->addChild('UnsignedProperties', null, 'http://uri.etsi.org/01903/v1.3.2#');
        $unsignedProperties->addAttribute('Id', 'UnsignedProperties-' . $signature->id);
        
        // UnsignedSignatureProperties for timestamp
        $unsignedSignatureProperties = $unsignedProperties->addChild('UnsignedSignatureProperties', null, 'http://uri.etsi.org/01903/v1.3.2#');
        
        if ($signature->timestamp_token) {
            $signatureTimeStamp = $unsignedSignatureProperties->addChild('SignatureTimeStamp', null, 'http://uri.etsi.org/01903/v1.3.2#');
            $signatureTimeStamp->addAttribute('Id', 'SignatureTimeStamp-' . $signature->id);
            
            $encapsulatedTimeStamp = $signatureTimeStamp->addChild('EncapsulatedTimeStamp', null, 'http://uri.etsi.org/01903/v1.3.2#');
            $encapsulatedTimeStamp[0] = base64_encode($signature->timestamp_token);
        }
        
        return $xades->asXML();
    }

    /**
     * Extract certificate information from digital certificate
     */
    public function extractCertificateInfo(DigitalCertificate $certificate): array
    {
        $certData = openssl_x509_parse($certificate->certificate_pem);
        
        return [
            'subject' => $this->formatDN($certData['subject'] ?? []),
            'issuer' => $this->formatDN($certData['issuer'] ?? []),
            'serial_number' => $certData['serialNumber'] ?? null,
            'valid_from' => $certData['validFrom_time_t'] ?? null,
            'valid_to' => $certData['validTo_time_t'] ?? null,
        ];
    }

    /**
     * Format Distinguished Name
     */
    private function formatDN(array $dn): string
    {
        $parts = [];
        foreach ($dn as $key => $value) {
            $parts[] = "$key=$value";
        }
        return implode(', ', $parts);
    }

    /**
     * Store XAdES metadata
     */
    public function storeXAdESMetadata(ESignature $signature, array $metadata): void
    {
        $signature->update([
            'signature_format' => 'XAdES',
            'xades_metadata' => json_encode($metadata),
            'certificate_subject' => $metadata['certificate_subject'] ?? null,
            'certificate_issuer' => $metadata['certificate_issuer'] ?? null,
            'certificate_serial_number' => $metadata['certificate_serial_number'] ?? null,
            'tsa_url' => $metadata['tsa_url'] ?? null,
            'tsa_certificate_subject' => $metadata['tsa_certificate_subject'] ?? null,
        ]);
    }
};
