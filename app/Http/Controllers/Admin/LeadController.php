<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin() && !auth()->user()->isManager()) {
                abort(403, 'Unauthorized access to leads management.');
            }
            return $next($request);
        });
    }

    // Show leads list
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $query = Lead::query();

        if ($currentUser->organization_id) {
            $query->where('organization_id', $currentUser->organization_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by email or name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('company_name', 'like', "%$search%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $leads = $query->paginate(20);

        // Get statistics
        $stats = [
            'total' => Lead::where('organization_id', $currentUser->organization_id)->count(),
            'new' => Lead::where('organization_id', $currentUser->organization_id)->new()->count(),
            'contacted' => Lead::where('organization_id', $currentUser->organization_id)->contacted()->count(),
            'qualified' => Lead::where('organization_id', $currentUser->organization_id)->qualified()->count(),
            'converted' => Lead::where('organization_id', $currentUser->organization_id)->converted()->count(),
            'this_month' => Lead::where('organization_id', $currentUser->organization_id)->thisMonth()->count(),
            'this_week' => Lead::where('organization_id', $currentUser->organization_id)->thisWeek()->count(),
            'conversion_rate' => $this->calculateConversionRate(),
        ];

        return view('admin.leads.index', compact('leads', 'stats'));
    }

    // Show lead details
    public function show(Lead $lead)
    {
        $this->authorizeLeadAccess($lead);
        return view('admin.leads.show', compact('lead'));
    }

    // Edit lead
    public function edit(Lead $lead)
    {
        $this->authorizeLeadAccess($lead);
        return view('admin.leads.edit', compact('lead'));
    }

    // Update lead
    public function update(Request $request, Lead $lead)
    {
        $this->authorizeLeadAccess($lead);

        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:150',
            'industry' => 'nullable|string|max:100',
            'status' => 'required|in:new,contacted,qualified,converted,rejected',
            'notes' => 'nullable|string',
        ]);

        // Track status change
        if ($lead->status !== $validated['status']) {
            if ($validated['status'] === 'contacted' && !$lead->contacted_at) {
                $validated['contacted_at'] = now();
            }
        }

        $lead->update($validated);

        Log::info('Lead updated', [
            'lead_id' => $lead->id,
            'email' => $lead->email,
            'status' => $validated['status'],
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.leads.show', $lead)
                       ->with('success', 'Lead đã được cập nhật thành công');
    }

    // Delete lead
    public function destroy(Lead $lead)
    {
        $this->authorizeLeadAccess($lead);

        $lead->delete();

        Log::info('Lead deleted', [
            'lead_id' => $lead->id,
            'email' => $lead->email,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.leads.index')
                       ->with('success', 'Lead đã được xóa thành công');
    }

    // Get statistics
    public function statistics()
    {
        $currentUser = auth()->user();
        
        $baseQuery = Lead::where('organization_id', $currentUser->organization_id);

        $stats = [
            'total_leads' => $baseQuery->count(),
            'new_leads' => $baseQuery->new()->count(),
            'contacted_leads' => $baseQuery->contacted()->count(),
            'qualified_leads' => $baseQuery->qualified()->count(),
            'converted_leads' => $baseQuery->converted()->count(),
            'this_month' => $baseQuery->thisMonth()->count(),
            'this_week' => $baseQuery->thisWeek()->count(),
            'conversion_rate' => $this->calculateConversionRate(),
            'leads_by_source' => $this->getLeadsBySource(),
            'leads_by_industry' => $this->getLeadsByIndustry(),
            'leads_by_date' => $this->getLeadsByDate(),
        ];

        return view('admin.leads.statistics', compact('stats'));
    }

    // Export leads to CSV
    public function export(Request $request)
    {
        $currentUser = auth()->user();
        $query = Lead::where('organization_id', $currentUser->organization_id);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $leads = $query->get();

        $filename = 'leads_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($leads) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID',
                'Họ tên',
                'Email',
                'Điện thoại',
                'Công ty',
                'Ngành',
                'Trạng thái',
                'Nguồn',
                'UTM Source',
                'UTM Medium',
                'UTM Campaign',
                'Ngày tạo',
                'Ghi chú',
            ]);

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->id,
                    $lead->full_name,
                    $lead->email,
                    $lead->phone,
                    $lead->company_name,
                    $lead->industry,
                    $lead->status_label,
                    $lead->source_label,
                    $lead->utm_source,
                    $lead->utm_medium,
                    $lead->utm_campaign,
                    $lead->created_at->format('Y-m-d H:i:s'),
                    $lead->notes,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function authorizeLeadAccess(Lead $lead)
    {
        $currentUser = auth()->user();
        if ($lead->organization_id !== $currentUser->organization_id) {
            abort(403, 'Unauthorized access to this lead.');
        }
    }

    // Get leads by source
    private function getLeadsBySource()
    {
        $currentUser = auth()->user();
        return Lead::where('organization_id', $currentUser->organization_id)
                   ->selectRaw('source, COUNT(*) as count')
                   ->groupBy('source')
                   ->get()
                   ->pluck('count', 'source')
                   ->toArray();
    }

    // Get leads by industry
    private function getLeadsByIndustry()
    {
        $currentUser = auth()->user();
        return Lead::where('organization_id', $currentUser->organization_id)
                   ->selectRaw('industry, COUNT(*) as count')
                   ->whereNotNull('industry')
                   ->groupBy('industry')
                   ->orderByRaw('count DESC')
                   ->limit(10)
                   ->get()
                   ->pluck('count', 'industry')
                   ->toArray();
    }

    // Get leads by date (last 30 days)
    private function getLeadsByDate()
    {
        $currentUser = auth()->user();
        return Lead::where('organization_id', $currentUser->organization_id)
                   ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                   ->where('created_at', '>=', now()->subDays(30))
                   ->groupBy('date')
                   ->orderBy('date')
                   ->get()
                   ->pluck('count', 'date')
                   ->toArray();
    }

    // Calculate conversion rate
    private function calculateConversionRate()
    {
        $currentUser = auth()->user();
        $total = Lead::where('organization_id', $currentUser->organization_id)->count();
        if ($total === 0) {
            return 0;
        }

        $converted = Lead::where('organization_id', $currentUser->organization_id)->converted()->count();
        return round(($converted / $total) * 100, 2);
    }
}
