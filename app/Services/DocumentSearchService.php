<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogService;

class DocumentSearchService
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Full-text search for documents
     * 
     * @param string $query Search query
     * @param array $filters Additional filters
     * @param int $perPage Results per page
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function search(string $query, array $filters = [], int $perPage = 15)
    {
        $searchQuery = Document::query();

        // Apply organization scope
        if (auth()->check()) {
            $searchQuery->where('organization_id', auth()->user()->organization_id);
        }

        // Full-text search on multiple fields
        if (!empty($query)) {
            $searchQuery->where(function ($q) use ($query) {
                $q->where('doc_number', 'LIKE', "%{$query}%")
                  ->orWhere('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhereRaw("JSON_EXTRACT(metadata, '$.keywords') LIKE ?", ["%{$query}%"])
                  ->orWhereRaw("JSON_EXTRACT(metadata, '$.tags') LIKE ?", ["%{$query}%"]);
            });
        }

        // Apply filters
        if (isset($filters['type']) && !empty($filters['type'])) {
            $searchQuery->where('type', $filters['type']);
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $searchQuery->where('status', $filters['status']);
        }

        if (isset($filters['uploaded_by']) && !empty($filters['uploaded_by'])) {
            $searchQuery->where('uploaded_by', $filters['uploaded_by']);
        }

        if (isset($filters['approved_by']) && !empty($filters['approved_by'])) {
            $searchQuery->where('approved_by', $filters['approved_by']);
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $searchQuery->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $searchQuery->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['expiring_soon']) && $filters['expiring_soon']) {
            $searchQuery->expiringWithin(30);
        }

        if (isset($filters['expired']) && $filters['expired']) {
            $searchQuery->whereNotNull('expiry_date')
                       ->where('expiry_date', '<', now());
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $searchQuery->orderBy($sortBy, $sortOrder);

        // Execute search
        $results = $searchQuery->with(['uploader', 'approver', 'organization'])
                              ->paginate($perPage);

        // Audit log
        $this->auditLogService->log(
            'DOCUMENT_SEARCH',
            'documents',
            null,
            null,
            [
                'query' => $query,
                'filters' => $filters,
                'results_count' => $results->total(),
            ]
        );

        return $results;
    }

    /**
     * Advanced search with MySQL full-text search (if available)
     */
    public function advancedSearch(string $query, array $filters = [], int $perPage = 15)
    {
        // Check if full-text index exists
        if (!$this->hasFullTextIndex()) {
            return $this->search($query, $filters, $perPage);
        }

        $searchQuery = Document::query();

        // Apply organization scope
        if (auth()->check()) {
            $searchQuery->where('organization_id', auth()->user()->organization_id);
        }

        // MySQL full-text search
        if (!empty($query)) {
            $searchQuery->whereRaw(
                "MATCH(doc_number, title, description) AGAINST(? IN BOOLEAN MODE)",
                [$query]
            );
        }

        // Apply same filters as regular search
        $this->applyFilters($searchQuery, $filters);

        // Sorting by relevance
        if (!empty($query)) {
            $searchQuery->selectRaw(
                "*, MATCH(doc_number, title, description) AGAINST(? IN BOOLEAN MODE) as relevance",
                [$query]
            )->orderBy('relevance', 'desc');
        }

        $results = $searchQuery->with(['uploader', 'approver', 'organization'])
                              ->paginate($perPage);

        // Audit log
        $this->auditLogService->log(
            'DOCUMENT_ADVANCED_SEARCH',
            'documents',
            null,
            null,
            [
                'query' => $query,
                'filters' => $filters,
                'results_count' => $results->total(),
            ]
        );

        return $results;
    }

    /**
     * Search by metadata fields
     */
    public function searchByMetadata(array $metadataFilters, int $perPage = 15)
    {
        $searchQuery = Document::query();

        // Apply organization scope
        if (auth()->check()) {
            $searchQuery->where('organization_id', auth()->user()->organization_id);
        }

        // Search in metadata JSON
        foreach ($metadataFilters as $key => $value) {
            $searchQuery->whereRaw(
                "JSON_EXTRACT(metadata, '$.{$key}') = ?",
                [$value]
            );
        }

        $results = $searchQuery->with(['uploader', 'approver', 'organization'])
                              ->paginate($perPage);

        // Audit log
        $this->auditLogService->log(
            'DOCUMENT_METADATA_SEARCH',
            'documents',
            null,
            null,
            [
                'metadata_filters' => $metadataFilters,
                'results_count' => $results->total(),
            ]
        );

        return $results;
    }

    /**
     * Get search suggestions based on partial query
     */
    public function getSuggestions(string $partialQuery, int $limit = 10): array
    {
        if (strlen($partialQuery) < 2) {
            return [];
        }

        $suggestions = Document::where('organization_id', auth()->user()->organization_id)
            ->where(function ($q) use ($partialQuery) {
                $q->where('doc_number', 'LIKE', "{$partialQuery}%")
                  ->orWhere('title', 'LIKE', "%{$partialQuery}%");
            })
            ->select('doc_number', 'title', 'type')
            ->limit($limit)
            ->get()
            ->map(function ($doc) {
                return [
                    'doc_number' => $doc->doc_number,
                    'title' => $doc->title,
                    'type' => $doc->type,
                    'display' => "{$doc->doc_number} - {$doc->title}",
                ];
            })
            ->toArray();

        return $suggestions;
    }

    /**
     * Get popular search terms
     */
    public function getPopularSearches(int $limit = 10): array
    {
        // This would require a search_logs table to track searches
        // For now, return empty array
        return [];
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['uploaded_by']) && !empty($filters['uploaded_by'])) {
            $query->where('uploaded_by', $filters['uploaded_by']);
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    /**
     * Check if full-text index exists on documents table
     */
    protected function hasFullTextIndex(): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM documents WHERE Index_type = 'FULLTEXT'");
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Export search results to CSV
     */
    public function exportSearchResults(string $query, array $filters = []): string
    {
        $results = $this->search($query, $filters, 10000)->items();

        $csvData = [];
        $csvData[] = ['Doc Number', 'Title', 'Type', 'Status', 'Uploaded By', 'Created At'];

        foreach ($results as $doc) {
            $csvData[] = [
                $doc->doc_number,
                $doc->title,
                $doc->type,
                $doc->status,
                $doc->uploader?->name ?? 'N/A',
                $doc->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $filename = 'document_search_' . now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        // Create directory if not exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        // Audit log
        $this->auditLogService->log(
            'DOCUMENT_SEARCH_EXPORT',
            'documents',
            null,
            null,
            [
                'query' => $query,
                'filters' => $filters,
                'results_count' => count($results),
                'filename' => $filename,
            ]
        );

        return $filepath;
    }
}
