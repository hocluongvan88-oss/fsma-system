<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ESignature;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    /**
     * Get audit trail with advanced filtering and export
     */
    public function query(Request $request)
    {
        $validated = $request->validate([
            'record_type' => 'nullable|string',
            'record_id' => 'nullable|integer',
            'user_id' => 'nullable|integer',
            'action' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'status' => 'nullable|in:active,revoked',
            'has_timestamp' => 'nullable|in:yes,no',
            'mfa_method' => 'nullable|in:totp,backup_code',
            'sort_by' => 'nullable|in:signed_at,user_id,record_type',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $query = ESignature::with(['user']);

        // Apply filters
        if ($validated['record_type'] ?? null) {
            $query->where('record_type', $validated['record_type']);
        }

        if ($validated['record_id'] ?? null) {
            $query->where('record_id', $validated['record_id']);
        }

        if ($validated['user_id'] ?? null) {
            $query->where('user_id', $validated['user_id']);
        }

        if ($validated['action'] ?? null) {
            $query->where('action', $validated['action']);
        }

        if ($validated['date_from'] ?? null) {
            $query->whereDate('signed_at', '>=', $validated['date_from']);
        }

        if ($validated['date_to'] ?? null) {
            $query->whereDate('signed_at', '<=', $validated['date_to']);
        }

        if ($validated['status'] ?? null) {
            if ($validated['status'] === 'active') {
                $query->where('is_revoked', false);
            } elseif ($validated['status'] === 'revoked') {
                $query->where('is_revoked', true);
            }
        }

        if ($validated['has_timestamp'] ?? null) {
            if ($validated['has_timestamp'] === 'yes') {
                $query->whereNotNull('timestamp_token');
            } elseif ($validated['has_timestamp'] === 'no') {
                $query->whereNull('timestamp_token');
            }
        }

        if ($validated['mfa_method'] ?? null) {
            $query->where('mfa_method', $validated['mfa_method']);
        }

        // Apply sorting
        $sortBy = $validated['sort_by'] ?? 'signed_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $validated['per_page'] ?? 50;
        $signatures = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $signatures->items(),
            'pagination' => [
                'total' => $signatures->total(),
                'per_page' => $signatures->perPage(),
                'current_page' => $signatures->currentPage(),
                'last_page' => $signatures->lastPage(),
                'from' => $signatures->firstItem(),
                'to' => $signatures->lastItem(),
            ],
        ]);
    }

    /**
     * Export audit trail to CSV
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'record_type' => 'nullable|string',
            'record_id' => 'nullable|integer',
            'user_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'status' => 'nullable|in:active,revoked',
        ]);

        $query = ESignature::with(['user']);

        // Apply filters
        if ($validated['record_type'] ?? null) {
            $query->where('record_type', $validated['record_type']);
        }

        if ($validated['record_id'] ?? null) {
            $query->where('record_id', $validated['record_id']);
        }

        if ($validated['user_id'] ?? null) {
            $query->where('user_id', $validated['user_id']);
        }

        if ($validated['date_from'] ?? null) {
            $query->whereDate('signed_at', '>=', $validated['date_from']);
        }

        if ($validated['date_to'] ?? null) {
            $query->whereDate('signed_at', '<=', $validated['date_to']);
        }

        if ($validated['status'] ?? null) {
            if ($validated['status'] === 'active') {
                $query->where('is_revoked', false);
            } elseif ($validated['status'] === 'revoked') {
                $query->where('is_revoked', true);
            }
        }

        $signatures = $query->orderBy('signed_at', 'desc')->get();

        // Generate CSV
        $filename = 'audit-trail-' . now()->format('Y-m-d-H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($signatures) {
            $file = fopen('php://output', 'w');

            // Write header
            fputcsv($file, [
                'Signed At',
                'User',
                'Email',
                'Record Type',
                'Record ID',
                'Action',
                'Meaning of Signature',
                'Reason',
                'IP Address',
                'Algorithm',
                'Status',
                'Revoked At',
                'Revocation Reason',
                'MFA Method',
                'Timestamp Provider',
            ]);

            // Write data
            foreach ($signatures as $signature) {
                fputcsv($file, [
                    $signature->signed_at->format('Y-m-d H:i:s'),
                    $signature->user->full_name,
                    $signature->user->email,
                    $signature->record_type,
                    $signature->record_id,
                    $signature->action,
                    $signature->meaning_of_signature,
                    $signature->reason,
                    $signature->ip_address,
                    $signature->signature_algorithm,
                    $signature->is_revoked ? 'Revoked' : 'Active',
                    $signature->revoked_at?->format('Y-m-d H:i:s'),
                    $signature->revocation_reason,
                    $signature->mfa_method,
                    $signature->timestamp_provider,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get audit trail statistics
     */
    public function statistics(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $query = ESignature::query();

        if ($validated['date_from'] ?? null) {
            $query->whereDate('signed_at', '>=', $validated['date_from']);
        }

        if ($validated['date_to'] ?? null) {
            $query->whereDate('signed_at', '<=', $validated['date_to']);
        }

        $stats = [
            'total_signatures' => $query->count(),
            'active_signatures' => $query->where('is_revoked', false)->count(),
            'revoked_signatures' => $query->where('is_revoked', true)->count(),
            'with_timestamp' => $query->whereNotNull('timestamp_token')->count(),
            'with_2fa' => $query->whereNotNull('mfa_method')->count(),
            'by_user' => $query->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->with('user')
                ->get()
                ->map(function ($item) {
                    return [
                        'user_id' => $item->user_id,
                        'user_name' => $item->user->full_name,
                        'count' => $item->count,
                    ];
                }),
            'by_action' => $query->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->get()
                ->map(function ($item) {
                    return [
                        'action' => $item->action,
                        'count' => $item->count,
                    ];
                }),
            'by_record_type' => $query->selectRaw('record_type, COUNT(*) as count')
                ->groupBy('record_type')
                ->get()
                ->map(function ($item) {
                    return [
                        'record_type' => $item->record_type,
                        'count' => $item->count,
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
