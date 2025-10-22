<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExportLog;
use Illuminate\Http\Request;

class ExportVerificationController extends Controller
{
    /**
     * Verify exported file integrity
     * POST /api/verify-export
     * 
     * Request body:
     * {
     *   "export_id": "EX-XXXXXXXXXX",
     *   "content_hash": "sha256_hash_here"
     * }
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'export_id' => 'required|string|exists:export_logs,export_id',
            'content_hash' => 'required|string|size:64', // SHA-256 hash is 64 chars
        ]);

        $exportLog = ExportLog::where('export_id', $validated['export_id'])->firstOrFail();

        // Compare hashes
        $isValid = hash_equals($exportLog->content_hash, $validated['content_hash']);

        if ($isValid) {
            $exportLog->markAsVerified('File integrity verified');
        } else {
            $exportLog->markAsAltered('Hash mismatch - file may have been modified');
        }

        return response()->json([
            'success' => true,
            'export_id' => $exportLog->export_id,
            'status' => $isValid ? 'valid' : 'altered',
            'message' => $isValid 
                ? 'Export file is authentic and has not been modified'
                : 'WARNING: Export file has been altered after export',
            'exported_by' => $exportLog->user?->name ?? 'System',
            'exported_at' => $exportLog->created_at->toIso8601String(),
            'record_count' => $exportLog->record_count,
            'file_type' => $exportLog->file_type,
            'verification_timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get export log details
     * GET /api/verify-export/{exportId}
     */
    public function getExportDetails(string $exportId)
    {
        $exportLog = ExportLog::where('export_id', $exportId)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'export_id' => $exportLog->export_id,
                'file_type' => $exportLog->file_type,
                'export_scope' => $exportLog->export_scope,
                'scope_value' => $exportLog->scope_value,
                'record_count' => $exportLog->record_count,
                'file_size' => $exportLog->file_size,
                'exported_by' => $exportLog->user?->name ?? 'System',
                'exported_at' => $exportLog->created_at->toIso8601String(),
                'is_verified' => $exportLog->is_verified,
                'verified_at' => $exportLog->verified_at?->toIso8601String(),
                'verification_notes' => $exportLog->verification_notes,
            ],
        ]);
    }

    /**
     * List recent exports
     * GET /api/verify-export/list?limit=20
     */
    public function listExports(Request $request)
    {
        $limit = $request->query('limit', 20);
        $exports = ExportLog::recentFirst()->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $exports->map(fn($export) => [
                'export_id' => $export->export_id,
                'file_type' => $export->file_type,
                'record_count' => $export->record_count,
                'exported_by' => $export->user?->name ?? 'System',
                'exported_at' => $export->created_at->toIso8601String(),
                'is_verified' => $export->is_verified,
            ]),
        ]);
    }
}
