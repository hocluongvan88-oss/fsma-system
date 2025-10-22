<?php

namespace App\Services;

use App\Models\ErrorLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ErrorTrackingService
{
    /**
     * Capture and log an error
     */
    public function captureError(Throwable $exception, array $context = [], $severity = 'error')
    {
        try {
            $errorHash = $this->generateErrorHash($exception);
            
            // Check if similar error already exists in last hour
            $existingError = ErrorLog::where('error_hash', $errorHash)
                ->where('created_at', '>=', now()->subHour())
                ->first();

            if ($existingError) {
                // Increment frequency
                $existingError->increment('frequency');
                return $existingError;
            }

            // Create new error log
            $errorLog = ErrorLog::create([
                'error_type' => class_basename($exception),
                'error_message' => $exception->getMessage(),
                'error_code' => $exception->getCode(),
                'file_path' => $exception->getFile(),
                'line_number' => $exception->getLine(),
                'stack_trace' => $this->parseStackTrace($exception),
                'context' => $context,
                'user_id' => auth()->id(),
                'url' => request()->fullUrl() ?? null,
                'method' => request()->method() ?? null,
                'ip_address' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,
                'error_hash' => $errorHash,
                'severity' => $severity,
                'frequency' => 1,
            ]);

            // Log to Laravel logs
            Log::error('Error captured', [
                'error_id' => $errorLog->id,
                'error_type' => $errorLog->error_type,
                'message' => $errorLog->error_message,
                'file' => $errorLog->file_path,
                'line' => $errorLog->line_number,
            ]);

            // Send notification for critical errors
            if ($severity === 'critical') {
                $this->notifyDevelopers($errorLog);
            }

            return $errorLog;
        } catch (Throwable $e) {
            // Fallback logging if error tracking fails
            Log::error('Error tracking service failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate unique hash for error grouping
     */
    private function generateErrorHash(Throwable $exception): string
    {
        $hashData = [
            class_basename($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        ];

        return hash('sha256', implode('|', $hashData));
    }

    /**
     * Parse stack trace into readable format
     */
    private function parseStackTrace(Throwable $exception): array
    {
        $trace = [];
        
        foreach ($exception->getTrace() as $frame) {
            $trace[] = [
                'file' => $frame['file'] ?? 'unknown',
                'line' => $frame['line'] ?? 0,
                'function' => $frame['function'] ?? 'unknown',
                'class' => $frame['class'] ?? null,
                'type' => $frame['type'] ?? null,
            ];
        }

        return $trace;
    }

    /**
     * Notify developers about critical errors
     */
    private function notifyDevelopers(ErrorLog $errorLog)
    {
        try {
            // Get admin users
            $admins = User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                // Send email notification
                if (env('MAIL_FROM_ADDRESS')) {
                    Mail::to($admin->email)->queue(new \App\Mail\ErrorNotificationMail($errorLog));
                }

                // Send Slack notification if configured
                if (env('SLACK_WEBHOOK_URL')) {
                    $this->sendSlackNotification($errorLog);
                }
            }

            Log::info('Error notification sent to developers', [
                'error_id' => $errorLog->id,
                'admin_count' => $admins->count(),
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to notify developers: ' . $e->getMessage());
        }
    }

    /**
     * Send Slack notification
     */
    private function sendSlackNotification(ErrorLog $errorLog)
    {
        try {
            $message = [
                'text' => '🚨 Critical Error in FSMA 204 System',
                'attachments' => [
                    [
                        'color' => 'danger',
                        'fields' => [
                            [
                                'title' => 'Error Type',
                                'value' => $errorLog->error_type,
                                'short' => true,
                            ],
                            [
                                'title' => 'Severity',
                                'value' => strtoupper($errorLog->severity),
                                'short' => true,
                            ],
                            [
                                'title' => 'Message',
                                'value' => $errorLog->error_message,
                                'short' => false,
                            ],
                            [
                                'title' => 'File',
                                'value' => $errorLog->file_path . ':' . $errorLog->line_number,
                                'short' => false,
                            ],
                            [
                                'title' => 'URL',
                                'value' => $errorLog->url,
                                'short' => false,
                            ],
                            [
                                'title' => 'User',
                                'value' => $errorLog->user?->email ?? 'Anonymous',
                                'short' => true,
                            ],
                            [
                                'title' => 'IP Address',
                                'value' => $errorLog->ip_address,
                                'short' => true,
                            ],
                        ],
                        'footer' => 'Error ID: ' . $errorLog->id,
                        'ts' => $errorLog->created_at->timestamp,
                    ],
                ],
            ];

            $ch = curl_init(env('SLACK_WEBHOOK_URL'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_exec($ch);
            curl_close($ch);
        } catch (Throwable $e) {
            Log::error('Failed to send Slack notification: ' . $e->getMessage());
        }
    }

    /**
     * Get error statistics
     */
    public function getErrorStats($days = 7)
    {
        $errors = ErrorLog::recent($days)->get();

        return [
            'total_errors' => $errors->count(),
            'unresolved_errors' => $errors->where('is_resolved', false)->count(),
            'critical_errors' => $errors->where('severity', 'critical')->count(),
            'by_type' => $errors->groupBy('error_type')->map->count(),
            'by_severity' => $errors->groupBy('severity')->map->count(),
            'most_frequent' => $errors->groupBy('error_hash')
                ->map->count()
                ->sortDesc()
                ->take(5),
        ];
    }

    /**
     * Get trending errors
     */
    public function getTrendingErrors($limit = 10)
    {
        return ErrorLog::unresolved()
            ->selectRaw('error_hash, COUNT(*) as count, MAX(created_at) as last_occurrence')
            ->groupBy('error_hash')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return ErrorLog::where('error_hash', $item->error_hash)->latest()->first();
            });
    }
}
