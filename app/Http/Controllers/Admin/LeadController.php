<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    // Show leads list
    public function index(Request $request)
    {
        $query = Lead::query();

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
            'total' => Lead::count(),
            'new' => Lead::new()->count(),
            'contacted' => Lead::contacted()->count(),
            'qualified' => Lead::qualified()->count(),
            'converted' => Lead::converted()->count(),
            'this_month' => Lead::thisMonth()->count(),
            'this_week' => Lead::thisWeek()->count(),
            'conversion_rate' => $this->calculateConversionRate(),
        ];

        return view('admin.leads.index', compact('leads', 'stats'));
    }

    // Show lead details
    public function show(Lead $lead)
    {
        return view('admin.leads.show', compact('lead'));
    }

    // Edit lead
    public function edit(Lead $lead)
    {
        return view('admin.leads.edit', compact('lead'));
    }

    // Update lead
    public function update(Request $request, Lead $lead)
    {
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
        ]);

        return redirect()->route('admin.leads.show', $lead)
                       ->with('success', 'Lead đã được cập nhật thành công');
    }

    // Delete lead
    public function destroy(Lead $lead)
    {
        $lead->delete();

        Log::info('Lead deleted', [
            'lead_id' => $lead->id,
            'email' => $lead->email,
        ]);

        return redirect()->route('admin.leads.index')
                       ->with('success', 'Lead đã được xóa thành công');
    }

    // Get statistics
    public function statistics()
    {
        $stats = [
            'total_leads' => Lead::count(),
            'new_leads' => Lead::new()->count(),
            'contacted_leads' => Lead::contacted()->count(),
            'qualified_leads' => Lead::qualified()->count(),
            'converted_leads' => Lead::converted()->count(),
            'this_month' => Lead::thisMonth()->count(),
            'this_week' => Lead::thisWeek()->count(),
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
        $query = Lead::query();

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

    // Get leads by source
    private function getLeadsBySource()
    {
        return Lead::selectRaw('source, COUNT(*) as count')
                   ->groupBy('source')
                   ->get()
                   ->pluck('count', 'source')
                   ->toArray();
    }

    // Get leads by industry
    private function getLeadsByIndustry()
    {
        return Lead::selectRaw('industry, COUNT(*) as count')
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
        return Lead::selectRaw('DATE(created_at) as date, COUNT(*) as count')
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
        $total = Lead::count();
        if ($total === 0) {
            return 0;
        }

        $converted = Lead::converted()->count();
        return round(($converted / $total) * 100, 2);
    }
}
