<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class DocumentApprovalService
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Initialize approval workflow for document
     */
    public function initializeApprovalWorkflow(Document $document, int $approvalLevels = 1): void
    {
        // Delete existing approvals if any
        $document->approvals()->delete();

        // Create approval records for each level
        for ($level = 1; $level <= $approvalLevels; $level++) {
            DocumentApproval::create([
                'document_id' => $document->id,
                'organization_id' => $document->organization_id,
                'approval_level' => $level,
                'status' => 'pending',
            ]);
        }

        $this->auditLogService->log(
            'INITIALIZE_APPROVAL_WORKFLOW',
            'documents',
            $document->id,
            null,
            ['approval_levels' => $approvalLevels]
        );
    }

    /**
     * Get current approval level for document
     */
    public function getCurrentApprovalLevel(Document $document): ?DocumentApproval
    {
        return $document->approvals()
            ->where('status', 'pending')
            ->orderBy('approval_level', 'asc')
            ->first();
    }

    /**
     * Get all approvals for document
     */
    public function getApprovalChain(Document $document): array
    {
        return $document->approvals()
            ->orderBy('approval_level', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Approve document at current level
     */
    public function approveAtLevel(Document $document, User $approver, ?string $notes = null): bool
    {
        $currentApproval = $this->getCurrentApprovalLevel($document);

        if (!$currentApproval) {
            throw new \Exception('No pending approvals for this document');
        }

        // Check if user has permission to approve at this level
        if (!$this->canApproveAtLevel($approver, $currentApproval->approval_level)) {
            throw new \Exception('User does not have permission to approve at level ' . $currentApproval->approval_level);
        }

        DB::beginTransaction();

        try {
            $currentApproval->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'notes' => $notes,
                'approved_at' => now(),
            ]);

            // Check if all approvals are complete
            $allApproved = !$document->approvals()
                ->where('status', '!=', 'approved')
                ->exists();

            if ($allApproved) {
                $document->update([
                    'status' => 'approved',
                    'approved_by' => $approver->id,
                    'approved_at' => now(),
                ]);
            } else {
                $document->update(['status' => 'review']);
            }

            $this->auditLogService->log(
                'APPROVE_DOCUMENT_LEVEL',
                'documents',
                $document->id,
                null,
                [
                    'approval_level' => $currentApproval->approval_level,
                    'approved_by' => $approver->id,
                    'notes' => $notes,
                    'all_approved' => $allApproved,
                ]
            );

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject document at current level
     */
    public function rejectAtLevel(Document $document, User $rejector, string $rejectionReason): bool
    {
        $currentApproval = $this->getCurrentApprovalLevel($document);

        if (!$currentApproval) {
            throw new \Exception('No pending approvals for this document');
        }

        DB::beginTransaction();

        try {
            $currentApproval->update([
                'status' => 'rejected',
                'approved_by' => $rejector->id,
                'rejection_reason' => $rejectionReason,
                'rejected_at' => now(),
            ]);

            // Reset document to draft and clear all subsequent approvals
            $document->update(['status' => 'draft']);
            
            $document->approvals()
                ->where('approval_level', '>', $currentApproval->approval_level)
                ->update(['status' => 'pending']);

            $this->auditLogService->log(
                'REJECT_DOCUMENT_LEVEL',
                'documents',
                $document->id,
                null,
                [
                    'approval_level' => $currentApproval->approval_level,
                    'rejected_by' => $rejector->id,
                    'rejection_reason' => $rejectionReason,
                ]
            );

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if user can approve at specific level
     */
    public function canApproveAtLevel(User $user, int $level): bool
    {
        // Level 1: Any user with approve_document permission
        if ($level === 1) {
            return $user->can('approve', Document::class);
        }

        // Level 2: Manager or higher
        if ($level === 2) {
            return $user->hasRole(['manager', 'admin']);
        }

        // Level 3: Admin or director
        if ($level === 3) {
            return $user->hasRole(['admin', 'director']);
        }

        return false;
    }

    /**
     * Get approval status summary
     */
    public function getApprovalStatus(Document $document): array
    {
        $approvals = $document->approvals()->orderBy('approval_level')->get();

        $summary = [
            'total_levels' => $approvals->count(),
            'approved_levels' => $approvals->where('status', 'approved')->count(),
            'pending_levels' => $approvals->where('status', 'pending')->count(),
            'rejected_levels' => $approvals->where('status', 'rejected')->count(),
            'completion_percentage' => 0,
            'current_level' => null,
            'approvals' => [],
        ];

        if ($summary['total_levels'] > 0) {
            $summary['completion_percentage'] = round(
                ($summary['approved_levels'] / $summary['total_levels']) * 100
            );
        }

        $currentApproval = $this->getCurrentApprovalLevel($document);
        if ($currentApproval) {
            $summary['current_level'] = $currentApproval->approval_level;
        }

        foreach ($approvals as $approval) {
            $summary['approvals'][] = [
                'level' => $approval->approval_level,
                'status' => $approval->status,
                'approved_by' => $approval->approver?->name,
                'approved_at' => $approval->approved_at,
                'notes' => $approval->notes,
                'rejection_reason' => $approval->rejection_reason,
            ];
        }

        return $summary;
    }

    /**
     * Get pending approvals for user
     */
    public function getPendingApprovalsForUser(User $user): array
    {
        $approvals = DocumentApproval::where('status', 'pending')
            ->where('organization_id', $user->organization_id)
            ->with('document')
            ->get();

        $userApprovals = [];
        foreach ($approvals as $approval) {
            if ($this->canApproveAtLevel($user, $approval->approval_level)) {
                $userApprovals[] = $approval;
            }
        }

        return $userApprovals;
    }
}
