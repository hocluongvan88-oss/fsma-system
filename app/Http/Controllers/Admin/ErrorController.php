<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use App\Services\ErrorTrackingService;
use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $query = ErrorLog::query();

        // Filter by severity
        if ($request->severity) {
            $query->where('severity', $request->severity);
        }

        // Filter by status
        if ($request->status === 'unresolved') {
            $query->unresolved();
        } elseif ($request->status === 'resolved') {
            $query->resolved();
        }

        // Filter by type
        if ($request->type) {
            $query->byType($request->type);
        }

        // Search
        if ($request->search) {
            $query->where('error_message', 'like', '%' . $request->search . '%')
                  ->orWhere('file_path', 'like', '%' . $request->search . '%');
        }

        $errors = $query->latest()->paginate(20);
        $stats = (new ErrorTrackingService())->getErrorStats();
        $trending = (new ErrorTrackingService())->getTrendingErrors(5);

        return view('admin.errors.index', compact('errors', 'stats', 'trending'));
    }

    public function show(ErrorLog $errorLog)
    {
        $similar = $errorLog->getSimilarErrors(10);
        
        return view('admin.errors.show', compact('errorLog', 'similar'));
    }

    public function resolve(Request $request, ErrorLog $errorLog)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $errorLog->markAsResolved(auth()->id(), $validated['notes'] ?? null);

        return back()->with('success', 'Error marked as resolved.');
    }

    public function delete(ErrorLog $errorLog)
    {
        $errorLog->delete();
        
        return back()->with('success', 'Error deleted.');
    }

    public function bulkResolve(Request $request)
    {
        $ids = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:error_logs,id',
        ])['ids'];

        ErrorLog::whereIn('id', $ids)->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return back()->with('success', count($ids) . ' errors marked as resolved.');
    }
}
