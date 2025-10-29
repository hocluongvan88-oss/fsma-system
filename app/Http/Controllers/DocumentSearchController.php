<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DocumentSearchService;

class DocumentSearchController extends Controller
{
    protected $searchService;

    public function __construct(DocumentSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Search documents
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'uploaded_by' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'expiring_soon' => 'nullable|boolean',
            'expired' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:doc_number,title,created_at,expiry_date',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $query = $validated['q'] ?? '';
        $filters = array_filter($validated, function ($key) {
            return $key !== 'q' && $key !== 'per_page';
        }, ARRAY_FILTER_USE_KEY);

        $perPage = $validated['per_page'] ?? 15;

        $results = $this->searchService->search($query, $filters, $perPage);

        return view('documents.search', [
            'results' => $results,
            'query' => $query,
            'filters' => $filters,
        ]);
    }

    /**
     * Advanced search with full-text
     */
    public function advancedSearch(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|max:255',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $query = $validated['q'];
        $filters = array_filter($validated, function ($key) {
            return $key !== 'q' && $key !== 'per_page';
        }, ARRAY_FILTER_USE_KEY);

        $perPage = $validated['per_page'] ?? 15;

        $results = $this->searchService->advancedSearch($query, $filters, $perPage);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Search by metadata
     */
    public function searchByMetadata(Request $request)
    {
        $validated = $request->validate([
            'metadata' => 'required|array',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 15;

        $results = $this->searchService->searchByMetadata($validated['metadata'], $perPage);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Get search suggestions
     */
    public function suggestions(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'nullable|integer|min:5|max:20',
        ]);

        $suggestions = $this->searchService->getSuggestions(
            $validated['q'],
            $validated['limit'] ?? 10
        );

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Export search results
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $query = $validated['q'] ?? '';
        $filters = array_filter($validated, function ($key) {
            return $key !== 'q';
        }, ARRAY_FILTER_USE_KEY);

        $filepath = $this->searchService->exportSearchResults($query, $filters);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }
}
