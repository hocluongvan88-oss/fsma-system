<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1e40af; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .content { background-color: #f9fafb; padding: 20px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .total-row { background-color: #dbeafe; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ __('messages.data_retention_cleanup_completed') }}</h2>
            <p>{{ __('messages.fsma_204_food_traceability_system') }}</p>
        </div>

        <div class="content">
            <p>{{ __('messages.hello_admin') }},</p>
            
            <p>{{ __('messages.automated_data_retention_cleanup_completed') }}</p>

            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.data_type') }}</th>
                        <th>{{ __('messages.records_deleted') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('messages.trace_records_27_months') }}</td>
                        <td>{{ $stats['trace_records'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.cte_events_27_months') }}</td>
                        <td>{{ $stats['cte_events'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.audit_logs_27_months') }}</td>
                        <td>{{ $stats['audit_logs'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.e_signatures_27_months') }}</td>
                        <td>{{ $stats['e_signatures'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.error_logs_6_months') }}</td>
                        <td>{{ $stats['error_logs'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.notifications_3_months') }}</td>
                        <td>{{ $stats['notifications'] }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>{{ __('messages.total_records_deleted') }}</td>
                        <td>{{ $totalDeleted }}</td>
                    </tr>
                </tbody>
            </table>

            <p><strong>{{ __('messages.retention_policy') }}:</strong></p>
            <ul>
                <li>{{ __('messages.traceability_data_retention_policy') }}</li>
                <li>{{ __('messages.compliance_data_retention_policy') }}</li>
                <li>{{ __('messages.error_logs_retention_policy') }}</li>
                <li>{{ __('messages.notifications_retention_policy') }}</li>
                <li>{{ __('messages.master_data_retention_policy') }}</li>
            </ul>

            <p>{{ __('messages.cleanup_helps_prevent_storage_issues') }}</p>

            <div class="footer">
                <p>{{ __('messages.automated_email_do_not_reply') }}</p>
                <p>{{ __('messages.copyright_vexim_global') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
