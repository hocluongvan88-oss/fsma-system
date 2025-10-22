<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LandingPageController extends Controller
{
    // Show landing page
    public function show()
    {
        return view('landing.fsma204');
    }

    // Store lead from landing page form
    public function storeLead(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'full_name' => 'required|string|max:100',
                'email' => 'required|email|max:100',
                'phone' => 'nullable|string|max:20',
                'company_name' => 'nullable|string|max:150',
                'industry' => 'nullable|string|max:100',
                'message' => 'nullable|string|max:1000',
                'g-recaptcha-response' => 'required|recaptcha',
            ], [
                'full_name.required' => 'Vui lòng nhập họ tên',
                'email.required' => 'Vui lòng nhập email',
                'email.email' => 'Email không hợp lệ',
                'g-recaptcha-response.required' => 'Vui lòng xác minh reCAPTCHA',
                'g-recaptcha-response.recaptcha' => 'reCAPTCHA xác minh thất bại',
            ]);

            // Check if email already exists
            $existingLead = Lead::where('email', $validated['email'])->first();
            if ($existingLead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email này đã được đăng ký trước đó',
                ], 422);
            }

            // Get UTM parameters from request
            $utmSource = $request->query('utm_source');
            $utmMedium = $request->query('utm_medium');
            $utmCampaign = $request->query('utm_campaign');
            $utmContent = $request->query('utm_content');

            // Create lead
            $lead = Lead::create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'company_name' => $validated['company_name'],
                'industry' => $validated['industry'],
                'message' => $validated['message'],
                'status' => 'new',
                'source' => 'landing_page',
                'utm_source' => $utmSource,
                'utm_medium' => $utmMedium,
                'utm_campaign' => $utmCampaign,
                'utm_content' => $utmContent,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Log::info('New lead created from landing page', [
                'lead_id' => $lead->id,
                'email' => $lead->email,
                'utm_source' => $utmSource,
            ]);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn bạn đã đăng ký! Chúng tôi sẽ liên hệ với bạn sớm.',
                'lead_id' => $lead->id,
                'redirect_url' => route('auth.register'), // Redirect to signup
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng kiểm tra lại thông tin',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating lead', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau.',
            ], 500);
        }
    }

    // Get lead statistics for admin dashboard
    public function getStatistics()
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
        ];

        return $stats;
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

    // Export leads to CSV
    public function exportLeads(Request $request)
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

        $leads = $query->get();

        // Create CSV
        $filename = 'leads_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($leads) {
            $file = fopen('php://output', 'w');
            
            // Header row
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

            // Data rows
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
}
