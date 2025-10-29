<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Validator;

class DocumentMetadataValidator
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Define metadata schema for documents
     */
    public function getMetadataSchema(): array
    {
        return [
            'document_category' => 'required|string|in:regulatory,operational,training,quality,other',
            'department' => 'required|string|max:100',
            'author' => 'nullable|string|max:100',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:50',
            'compliance_requirements' => 'nullable|array',
            'compliance_requirements.*' => 'string|in:FSMA_204,FDA_21_CFR_11,ISO_9001,GMP,HACCP',
            'retention_period_months' => 'required|integer|min:0|max:600',
            'classification' => 'required|string|in:public,internal,confidential,restricted',
            'version_control_enabled' => 'required|boolean',
            'requires_approval' => 'required|boolean',
            'approval_levels' => 'required_if:requires_approval,true|integer|min:1|max:3',
            'related_documents' => 'nullable|array',
            'related_documents.*' => 'integer|exists:documents,id',
            'custom_fields' => 'nullable|array',
        ];
    }

    /**
     * Validate metadata structure
     */
    public function validateMetadata(array $metadata): array
    {
        $schema = $this->getMetadataSchema();
        
        $validator = Validator::make($metadata, $schema);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
            ];
        }

        return [
            'valid' => true,
            'errors' => [],
            'validated_data' => $validator->validated(),
        ];
    }

    /**
     * Validate and store metadata for document
     */
    public function storeMetadata(Document $document, array $metadata): bool
    {
        $validation = $this->validateMetadata($metadata);

        if (!$validation['valid']) {
            throw new \Exception('Invalid metadata: ' . json_encode($validation['errors']));
        }

        if (empty($validation['validated_data']['author'])) {
            $validation['validated_data']['author'] = auth()->user()->name ?? 'System';
        }

        // Calculate metadata hash for integrity
        $fileIntegrityService = app(FileIntegrityService::class);
        $metadataHash = $fileIntegrityService->calculateMetadataHash($validation['validated_data']);

        $document->update([
            'metadata' => $validation['validated_data'],
            'metadata_hash' => $metadataHash,
        ]);

        $this->auditLogService->log(
            'UPDATE_METADATA',
            'documents',
            $document->id,
            null,
            [
                'metadata_hash' => $metadataHash,
                'metadata_keys' => array_keys($validation['validated_data']),
            ]
        );

        return true;
    }

    /**
     * Get metadata template for document type
     */
    public function getMetadataTemplate(string $documentType): array
    {
        $templates = [
            'traceability_plan' => [
                'document_category' => 'regulatory',
                'department' => 'Quality Assurance',
                'compliance_requirements' => ['FSMA_204'],
                'retention_period_months' => 60,
                'classification' => 'confidential',
                'version_control_enabled' => true,
                'requires_approval' => true,
                'approval_levels' => 2,
            ],
            'sop' => [
                'document_category' => 'operational',
                'department' => 'Operations',
                'compliance_requirements' => ['GMP', 'HACCP'],
                'retention_period_months' => 36,
                'classification' => 'internal',
                'version_control_enabled' => true,
                'requires_approval' => true,
                'approval_levels' => 1,
            ],
            'fda_correspondence' => [
                'document_category' => 'regulatory',
                'department' => 'Regulatory Affairs',
                'compliance_requirements' => ['FDA_21_CFR_11'],
                'retention_period_months' => 120,
                'classification' => 'restricted',
                'version_control_enabled' => true,
                'requires_approval' => true,
                'approval_levels' => 3,
            ],
            'training_material' => [
                'document_category' => 'training',
                'department' => 'Human Resources',
                'compliance_requirements' => [],
                'retention_period_months' => 24,
                'classification' => 'internal',
                'version_control_enabled' => true,
                'requires_approval' => false,
                'approval_levels' => 1,
            ],
            'audit_report' => [
                'document_category' => 'quality',
                'department' => 'Quality Assurance',
                'compliance_requirements' => ['ISO_9001'],
                'retention_period_months' => 60,
                'classification' => 'confidential',
                'version_control_enabled' => true,
                'requires_approval' => true,
                'approval_levels' => 2,
            ],
            'other' => [
                'document_category' => 'other',
                'department' => 'General',
                'compliance_requirements' => [],
                'retention_period_months' => 12,
                'classification' => 'internal',
                'version_control_enabled' => false,
                'requires_approval' => false,
                'approval_levels' => 1,
            ],
        ];

        return $templates[$documentType] ?? $templates['other'];
    }

    /**
     * Enrich metadata with defaults
     */
    public function enrichMetadata(array $metadata, string $documentType): array
    {
        $template = $this->getMetadataTemplate($documentType);
        
        // Merge with template, keeping provided values
        $enriched = array_merge($template, $metadata);

        return $enriched;
    }

    /**
     * Track metadata changes
     */
    public function trackMetadataChange(Document $document, array $oldMetadata, array $newMetadata): void
    {
        $changes = [];

        foreach ($newMetadata as $key => $value) {
            $oldValue = $oldMetadata[$key] ?? null;
            if ($oldValue !== $value) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $value,
                ];
            }
        }

        if (!empty($changes)) {
            $this->auditLogService->log(
                'METADATA_CHANGED',
                'documents',
                $document->id,
                null,
                [
                    'changes' => $changes,
                    'changed_fields' => array_keys($changes),
                ]
            );
        }
    }

    /**
     * Validate metadata compliance with FSMA 204
     */
    public function validateFSMACompliance(array $metadata): array
    {
        $issues = [];

        // Check required FSMA fields
        if (empty($metadata['compliance_requirements']) || 
            !in_array('FSMA_204', $metadata['compliance_requirements'])) {
            $issues[] = 'FSMA 204 compliance requirement not specified';
        }

        if ($metadata['retention_period_months'] < 12) {
            $issues[] = 'FSMA 204 requires minimum 12 months retention';
        }

        if ($metadata['classification'] !== 'confidential' && $metadata['classification'] !== 'restricted') {
            $issues[] = 'FSMA 204 documents should be classified as confidential or restricted';
        }

        if (!$metadata['version_control_enabled']) {
            $issues[] = 'FSMA 204 requires version control to be enabled';
        }

        return [
            'compliant' => count($issues) === 0,
            'issues' => $issues,
        ];
    }
}
