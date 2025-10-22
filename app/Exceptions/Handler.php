<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Services\ErrorTrackingService;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Determine severity based on exception type
            $severity = $this->determineSeverity($e);
            
            // Capture error with tracking service
            $errorTracker = new ErrorTrackingService();
            $errorTracker->captureError($e, [
                'exception_class' => get_class($e),
                'timestamp' => now()->toIso8601String(),
            ], $severity);
        });
    }

    /**
     * Determine error severity
     */
    private function determineSeverity(Throwable $e): string
    {
        $criticalExceptions = [
            'PDOException',
            'QueryException',
            'FatalThrowableError',
            'Error',
        ];

        if (in_array(class_basename($e), $criticalExceptions)) {
            return 'critical';
        }

        if ($e->getCode() >= 500) {
            return 'critical';
        }

        if ($e->getCode() >= 400) {
            return 'warning';
        }

        return 'error';
    }
}
